<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\Product;
use App\Models\Voucher;
use App\Services\Accounting\ItemVoucherService;
use App\Services\Accounting\PartyOutstandingService;
use App\Services\Accounting\StockReceiptService;
use App\Services\Accounting\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tally-style vouchers: Contra, Payment, Receipt and Journal (slice 2).
 *
 * Every voucher type is gated by its own access-control module
 * (config/accounting.php `module`), so authorisation is resolved from the
 * voucher's type rather than a single shared permission.
 */
class VoucherController extends Controller
{
    public function __construct(
        protected VoucherService $vouchers,
        protected ItemVoucherService $items,
        protected PartyOutstandingService $outstanding,
        protected StockReceiptService $stockReceipts,
    ) {
    }

    public function index(Request $request)
    {
        $types = $this->viewableTypes($request);
        if (empty($types)) {
            abort(403, 'You do not have access to any voucher type.');
        }

        $query = Voucher::with($this->vouchers->relations());

        // The stock block on each row is derived from its lines, so load them
        // up-front: without this the register is an N+1 over `voucher_items`.
        if (collect($types)->contains(fn ($type) => $this->isItemType($type))) {
            $query->with('items.product:id,name,sku');
        }

        $requested = $request->input('voucher_type');
        if ($requested) {
            $requested = is_array($requested) ? $requested : array_map('trim', explode(',', (string) $requested));
            $requested = array_values(array_intersect($requested, $types));
            if (empty($requested)) {
                abort(403, 'You do not have access to that voucher type.');
            }
            $query->whereIn('voucher_type', $requested);
        } else {
            $query->whereIn('voucher_type', $types);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('narration', 'like', "%{$search}%")
                    ->orWhereHas('party', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('entries.ledger', fn ($l) => $l->where('name', 'like', "%{$search}%"));
            });
        }

        if ($ledgerId = $request->input('ledger_id')) {
            $query->whereHas('entries', fn ($q) => $q->where('ledger_id', $ledgerId));
        }

        if ($request->filled('from')) {
            $query->whereDate('voucher_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('voucher_date', '<=', $request->input('to'));
        }

        $vouchers = $query->orderByDesc('voucher_date')->orderByDesc('id')->get()
            ->map(fn (Voucher $voucher) => $this->withStockSummary($voucher));

        return response()->json($vouchers);
    }

    public function store(Request $request)
    {
        $type = $this->assertKnownType($request);
        $this->authorize($this->moduleFor($type) . '.create');
        if ($this->isItemType($type)) {
            $voucher = $this->items->create($this->validatedItem($request), $request->user()->id);
        } else {
            $voucher = $this->vouchers->create($this->validated($request), $request->user()->id);
        }
        return response()->json($this->withStockSummary($voucher), 201);
    }

    public function show(Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.view');
        $voucher->load($this->serviceFor($voucher->voucher_type)->relations());

        return response()->json($this->withStockSummary($voucher));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.update');
        $type = $this->assertKnownType($request, $voucher->voucher_type);
        $this->authorize($this->moduleFor($type) . '.update');
        if ($this->isItemType($type)) {
            $voucher = $this->items->update($voucher, $this->validatedItem($request), $request->user()->id);
        } else {
            $voucher = $this->vouchers->update($voucher, $this->validated($request), $request->user()->id);
        }
        return response()->json($this->withStockSummary($voucher));
    }

    public function destroy(Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.delete');
        $this->serviceFor($voucher->voucher_type)->delete($voucher);
        return response()->noContent();
    }

    public function partyOutstanding(Request $request, LedgerAccount $ledger)
    {
        if (!$this->canViewBillWise($request)) {
            abort(403, 'You do not have access to bill-wise details.');
        }
        return response()->json($this->outstanding->forLedger($ledger));
    }

    public function meta(Request $request)
    {
        if (empty($this->viewableTypes($request))) {
            abort(403, 'You do not have access to any voucher type.');
        }
        return response()->json([
            'defaults' => $this->items->defaultLedgers(),
            'gst_rates' => config('accounting.gst_rates', [0, 5, 12, 18, 28]),
            'bill_types' => config('accounting.bill_types', []),
            // Which item types the warehouse receives (or delivers) against,
            // so the form knows whether to show the stock panel at all.
            'stock_types' => collect(config('accounting.voucher_types'))
                ->filter(fn (array $type) => ! empty($type['stock']))
                ->map(fn (array $type) => $type['stock'])
                ->all(),
            'stock_statuses' => config('accounting.stock_tracking.statuses', []),
        ]);
    }

    /**
     * Whether a receipt may be keyed against this document.
     *
     * `posted_purchases` is the one to watch: a purchase bill may be recorded
     * before the goods arrive (supplier invoices in advance), so the warehouse
     * still has work to do. Item types that never move stock are omitted.
     *
     * @return array<string, mixed>
     */
    protected function stockMetaFor(string $type, $voucher = null): array
    {
        $direction = config("accounting.voucher_types.{$type}.stock");

        if ($direction === null) {
            return ['tracks_stock' => false, 'available' => false, 'mode' => null];
        }

        $outbound = $direction === 'out';
        $status = $voucher ? $voucher->status : null;

        return [
            'tracks_stock' => true,
            'available' => true,
            'mode' => $outbound ? 'delivery' : 'receipt',
            'direction' => $direction,
            // Only a booked bill is received; a draft has not hit the books yet.
            'blocked_reason' => ($status !== null && $status !== 'posted')
                ? 'Post the voucher before recording goods against it.'
                : null,
        ];
    }

    /**
     * Attach the receipt summary to a voucher payload.
     *
     * The register and the detail view both need to answer "has this arrived?"
     * without a second round trip, so every item voucher carries its own
     * `stock` block: direction, derived status, ordered / moved / pending
     * totals, and the receipt history.
     */
    protected function withStockSummary(Voucher $voucher): Voucher
    {
        if (! $this->isItemType($voucher->voucher_type)) {
            return $voucher;
        }

        try {
            $voucher->setAttribute('stock', array_merge(
                $this->stockMetaFor($voucher->voucher_type, $voucher),
                $this->stockReceipts->summary($voucher),
            ));
        } catch (\Throwable $e) {
            // The register must never go down because the warehouse half of it
            // is unavailable (e.g. the stock_receipts migration has not run on
            // this database yet). Degrade to "no stock data" and carry on.
            $voucher->setAttribute('stock', array_merge(
                $this->stockMetaFor($voucher->voucher_type, $voucher),
                ['status' => null, 'lines' => [], 'receipts' => [], 'unavailable' => true],
            ));
        }

        return $voucher;
    }

    /**
     * On-hand stock per line, for the voucher form's availability column.
     *
     * Read-only on purpose: an accountant keying a sale should see that the
     * warehouse is short, but a shortfall must not stop the sale being booked.
     */
    public function stockAvailability(Request $request, Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.view');

        return response()->json($this->stockReceipts->availability($voucher));
    }

    /**
     * The products a voucher line can pick, keyed by `voucher_type`.
     *
     * Authorised through the voucher's own module so an accountant who can key
     * purchase / sales vouchers does not also need the products module.
     */
    public function products(Request $request)
    {
        $type = $this->assertKnownType($request);

        if (!$this->isItemType($type)) {
            abort(422, 'This voucher type does not carry line items.');
        }

        $this->authorize($this->moduleFor($type) . '.view');

        return response()->json(
            Product::query()
                ->with('unit:id,name')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'hsn_code', 'dimension', 'description', 'unit_id'])
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'hsn_code' => $p->hsn_code,
                    'dimension' => $p->dimension,
                    'description' => $p->description,
                    'unit' => $p->unit?->name,
                ])
        );
    }

    protected function viewableTypes(Request $request): array
    {
        $user = $request->user();
        return array_values(array_filter(
            array_keys(config('accounting.voucher_types')),
            function (string $type) use ($user) {
                $module = $this->moduleFor($type);
                return $module !== null && $user->can("{$module}.view");
            }
        ));
    }

    protected function canViewBillWise(Request $request): bool
    {
        foreach (config('accounting.voucher_types') as $type => $config) {
            if (empty($config['bills'])) {
                continue;
            }
            $module = $this->moduleFor($type);
            if ($module && $request->user()->can("{$module}.view")) {
                return true;
            }
        }
        return false;
    }

    protected function moduleFor(string $type): ?string
    {
        $module = config("accounting.voucher_types.{$type}.module");
        return $module ?: null;
    }

    protected function assertKnownType(Request $request, ?string $default = null): string
    {
        $type = $request->input('voucher_type', $default);
        if (!$type || !isset(config('accounting.voucher_types')[$type])) {
            abort(422, 'Unknown voucher type.');
        }
        if (!$this->moduleFor($type)) {
            abort(422, 'This voucher type is not available yet.');
        }
        return $type;
    }

    protected function isItemType(string $type): bool
    {
        return (bool) config("accounting.voucher_types.{$type}.items");
    }

    protected function serviceFor(string $type)
    {
        return $this->isItemType($type) ? $this->items : $this->vouchers;
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'voucher_type' => ['required', Rule::in(array_keys(config('accounting.voucher_types')))],
            'voucher_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'narration' => 'nullable|string|max:2000',
            'status' => ['nullable', Rule::in(config('accounting.statuses'))],
            'party_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'entries' => 'required|array|min:2',
            'entries.*.ledger_id' => 'required|integer|exists:ledger_accounts,id',
            'entries.*.entry_type' => ['required', Rule::in(['Dr', 'Cr'])],
            'entries.*.amount' => 'required|numeric|gt:0',
            'entries.*.narration' => 'nullable|string|max:255',
            'bill_allocations' => 'nullable|array',
            'bill_allocations.*.bill_type' => ['required', Rule::in(config('accounting.bill_types'))],
            'bill_allocations.*.bill_id' => 'nullable|integer',
            'bill_allocations.*.bill_name' => 'nullable|string|max:255',
            'bill_allocations.*.amount' => 'required|numeric|gt:0',
        ]);
    }

    protected function validatedItem(Request $request): array
    {
        return $request->validate([
            'voucher_type' => ['required', Rule::in(array_keys(config('accounting.voucher_types')))],
            'voucher_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'narration' => 'nullable|string|max:2000',
            'status' => ['nullable', Rule::in(config('accounting.statuses'))],
            'party_ledger_id' => 'required|integer|exists:ledger_accounts,id',
            // Defaults to party credit days + voucher date when omitted.
            'due_date' => 'nullable|date',
            'gst_type' => ['nullable', Rule::in(['intra', 'inter'])],
            'round_off' => 'nullable|numeric',
            // Tax and discount are document-level; the line only carries what
            // the item is (description, HSN, unit) and how much at what rate.
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_type' => ['nullable', Rule::in(['percent', 'amount'])],
            'discount_value' => 'nullable|numeric|min:0',
            'base_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'cgst_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'sgst_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'igst_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'round_off_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            // Notes move stock on save; the caller may say from where. Blank
            // falls back to the first site.
            'location_type' => 'nullable|string|max:255',
            'location_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.hsn_code' => 'nullable|string|max:32',
            'items.*.unit' => 'nullable|string|max:16',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.rate' => 'required|numeric|min:0',
            'bill_allocations' => 'nullable|array',
            'bill_allocations.*.bill_type' => ['required', Rule::in(config('accounting.bill_types'))],
            'bill_allocations.*.bill_id' => 'nullable|integer',
            'bill_allocations.*.bill_name' => 'nullable|string|max:255',
            'bill_allocations.*.amount' => 'required|numeric|gt:0',
        ]);
    }
}