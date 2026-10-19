<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\Product;
use App\Models\Voucher;
use App\Services\Accounting\ItemVoucherService;
use App\Services\Accounting\PartyOutstandingService;
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
    ) {
    }

    public function index(Request $request)
    {
        $types = $this->viewableTypes($request);
        if (empty($types)) {
            abort(403, 'You do not have access to any voucher type.');
        }

        $query = Voucher::with($this->vouchers->relations());

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

        $vouchers = $query->orderByDesc('voucher_date')->orderByDesc('id')->get();
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
        return response()->json($voucher, 201);
    }

    public function show(Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.view');
        return response()->json($voucher->load($this->serviceFor($voucher->voucher_type)->relations()));
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
        return response()->json($voucher);
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
        ]);
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
            Product::query()->orderBy('name')->get(['id', 'name', 'hsn_code'])
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
            'base_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'cgst_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'sgst_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'igst_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'round_off_ledger_id' => 'nullable|integer|exists:ledger_accounts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.hsn_code' => 'nullable|string|max:32',
            'items.*.unit' => 'nullable|string|max:16',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'bill_allocations' => 'nullable|array',
            'bill_allocations.*.bill_type' => ['required', Rule::in(config('accounting.bill_types'))],
            'bill_allocations.*.bill_id' => 'nullable|integer',
            'bill_allocations.*.bill_name' => 'nullable|string|max:255',
            'bill_allocations.*.amount' => 'required|numeric|gt:0',
        ]);
    }
}