<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\StockReceipt;
use App\Models\Voucher;
use App\Services\Accounting\StockReceiptService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Goods receipt notes (and delivery notes) against item vouchers.
 *
 * The purchase / sales voucher is the bill; these endpoints answer the
 * warehouse's half of the question: how much of the bill has actually arrived,
 * when, and where it went.
 */
class StockReceiptController extends Controller
{
    public function __construct(protected StockReceiptService $receipts)
    {
    }

    /**
     * The receipt register, optionally narrowed to one voucher.
     */
    public function index(Request $request)
    {
        $this->authorize('stock-receipts.view');

        $filters = $request->validate([
            'voucher_type' => ['nullable', Rule::in(array_keys(config('accounting.voucher_types')))],
            'status' => ['nullable', Rule::in(['active', 'reversed'])],
            'search' => 'nullable|string|max:255',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        return response()->json($this->receipts->register($filters));
    }

    /**
     * Every receipt (and the per-line maths) for one voucher.
     */
    public function forVoucher(Request $request, Voucher $voucher)
    {
        $this->authorize('stock-receipts.view');
        $this->authorizeViewingVoucher($request, $voucher);

        return response()->json($this->receipts->summary($voucher));
    }

    /**
     * Record a delivery. Lines carrying no product, or more than is still
     * outstanding, are refused before anything is written.
     */
    public function store(Request $request, Voucher $voucher)
    {
        $this->authorize('stock-receipts.create');
        $this->authorizeViewingVoucher($request, $voucher);

        $data = $request->validate([
            'receipt_date' => 'required|date',
            'location_type' => 'nullable|string|max:191',
            'location_id' => 'nullable|integer',
            'supplier_bill_no' => 'nullable|string|max:255',
            'supplier_bill_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.voucher_item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.batch_no' => 'nullable|string|max:191',
        ]);

        $receipt = $this->receipts->create($voucher, $data, $request->user()->id);

        return response()->json([
            'receipt' => $receipt,
            'summary' => $this->receipts->summary($voucher->fresh()),
        ], 201);
    }

    public function show(Request $request, StockReceipt $receipt)
    {
        $this->authorize('stock-receipts.view');

        $receipt->load($this->receipts->relations());

        return response()->json($receipt);
    }

    /**
     * Reverse a receipt. The row is kept and stamped; a balancing stock
     * movement is written, so the counters and the ledger stay in step.
     */
    public function destroy(Request $request, StockReceipt $receipt)
    {
        $this->authorize('stock-receipts.delete');

        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $this->receipts->reverse($receipt, $request->user()->id, $data['reason'] ?? null);

        return response()->json([
            'summary' => $this->receipts->summary($receipt->voucher()->firstOrFail()),
        ]);
    }

    /**
     * A receipt is only visible to somebody who can see the bill it fulfils.
     */
    protected function authorizeViewingVoucher(Request $request, Voucher $voucher): void
    {
        $module = config("accounting.voucher_types.{$voucher->voucher_type}.module");

        if (! $module || ! $request->user()->can("{$module}.view")) {
            abort(403, 'You do not have access to this document.');
        }
    }
}
