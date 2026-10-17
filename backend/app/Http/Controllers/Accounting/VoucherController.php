<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\Voucher;
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
        protected VoucherService $service,
        protected PartyOutstandingService $outstanding,
    ) {
    }

    public function index(Request $request)
    {
        $types = $this->viewableTypes($request);
        if (empty($types)) {
            abort(403, 'You do not have access to any voucher type.');
        }

        $query = Voucher::with($this->service->relations());

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

        return response()->json(
            $query->orderByDesc('voucher_date')->orderByDesc('id')->get()
        );
    }

    public function store(Request $request)
    {
        $type = $this->assertKnownType($request);
        $this->authorize($this->moduleFor($type) . '.create');

        $voucher = $this->service->create($this->validated($request), $request->user()->id);

        return response()->json($voucher, 201);
    }

    public function show(Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.view');

        return response()->json($voucher->load($this->service->relations()));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.update');

        $type = $this->assertKnownType($request, $voucher->voucher_type);
        $this->authorize($this->moduleFor($type) . '.update');

        $voucher = $this->service->update($voucher, $this->validated($request), $request->user()->id);

        return response()->json($voucher);
    }

    public function destroy(Voucher $voucher)
    {
        $this->authorize($this->moduleFor($voucher->voucher_type) . '.delete');

        $this->service->delete($voucher);

        return response()->noContent();
    }

    /**
     * Outstanding bills for a party ledger, used to fill the bill-wise panel of
     * a Payment / Receipt / Journal voucher.
     */
    public function partyOutstanding(Request $request, LedgerAccount $ledger)
    {
        if (! $this->canViewBillWise($request)) {
            abort(403, 'You do not have access to bill-wise details.');
        }

        return response()->json($this->outstanding->forLedger($ledger));
    }

    /* ------------------------------ internals ----------------------------- */

    /** Voucher types the signed-in user may at least view. */
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

    /** Access-control module for a voucher type, or null when not yet exposed. */
    protected function moduleFor(string $type): ?string
    {
        $module = config("accounting.voucher_types.{$type}.module");

        return $module ?: null;
    }

    /** Validate the type and make sure it is exposed to the API. */
    protected function assertKnownType(Request $request, ?string $default = null): string
    {
        $type = $request->input('voucher_type', $default);

        if (! $type || ! isset(config('accounting.voucher_types')[$type])) {
            abort(422, 'Unknown voucher type.');
        }

        if (! $this->moduleFor($type)) {
            abort(422, 'This voucher type is not available yet.');
        }

        return $type;
    }

    /**
     * @return array<string, mixed>
     */
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
}
