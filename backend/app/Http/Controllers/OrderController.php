<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Accounting\AccountingReportService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Order tracking (operational): list, raise, show, edit, advance, cancel.
 *
 * Every route is gated on the `orders` access module (config/access.php) and
 * nothing here writes to the ledger — an order is a status record, not a
 * voucher. The payment block on `show` is read straight from the Outstanding
 * report, so what an order claims to be owed can never drift from the books.
 */
class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orders,
        protected AccountingReportService $reports,
    ) {
    }

    /* ------------------------------- list ------------------------------- */

    public function index(Request $request)
    {
        $this->authorize('orders.view');

        $query = Order::query()->withCount('items')->with(['customer:id,name', 'supplier:id,name']);

        if ($side = $request->input('side')) {
            $query->where('side', $side);
        }

        if ($status = $request->input('status')) {
            $query->whereIn('status', explode(',', (string) $status));
        }

        if ($partyId = $request->integer('party_id')) {
            $query->where(function ($q) use ($partyId) {
                $q->where('customer_id', $partyId)->orWhere('supplier_id', $partyId);
            });
        }

        if ($from = $request->input('from')) {
            $query->whereDate('order_date', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('order_date', '<=', $to);
        }

        if ($search = trim((string) $request->input('search'))) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('order_no', 'like', $like)
                    ->orWhere('reference_no', 'like', $like)
                    ->orWhere('notes', 'like', $like)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', $like));
            });
        }

        return response()->json(
            $query->orderByDesc('order_date')->orderByDesc('id')->get()
        );
    }

    /* ------------------------------- write ------------------------------ */

    public function store(Request $request)
    {
        $this->authorize('orders.create');

        $order = $this->orders->create($this->validated($request), $request->user()->id);

        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        $this->authorize('orders.view');

        $order->load([
            'items',
            'events.user:id,name',
            'customer:id,name',
            'supplier:id,name',
            'creator:id,name',
        ]);

        return response()->json($order->toArray() + [
            'document' => $this->document($order),
            'payment' => $this->payment($order),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('orders.update');

        $order = $this->orders->update($order, $this->validated($request, false), $request->user()->id);

        return response()->json($order);
    }

    /* ------------------------------ pipeline ---------------------------- */

    /** Move the order one stage; reaching `invoiced` carries the document link. */
    public function advance(Request $request, Order $order)
    {
        $this->authorize('orders.fulfil');

        $data = $request->validate([
            'document_type' => ['nullable', Rule::in(config('accounting.bill_types'))],
            'document_id' => 'nullable|integer',
            'note' => 'nullable|string|max:1000',
        ]);

        return response()->json($this->orders->advance($order, $data, $request->user()->id));
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('orders.cancel');

        $data = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        return response()->json($this->orders->cancel($order, $data['note'] ?? null, $request->user()->id));
    }

    /* ----------------------------- validation --------------------------- */

    /** @return array<string, mixed> */
    protected function validated(Request $request, bool $requireItems = true): array
    {
        return $request->validate([
            'side' => [$requireItems ? 'required' : 'nullable', Rule::in(array_keys(config('orders.sides')))],
            'customer_id' => 'nullable|integer|exists:customers,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'order_date' => $requireItems ? 'required|date' : 'nullable|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'items' => $requireItems ? 'required|array|min:1' : 'nullable|array',
            'items.*.product_id' => 'nullable|integer',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.hsn_code' => 'nullable|string|max:32',
            'items.*.unit' => 'nullable|string|max:16',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);
    }

    /* ------------------------------ reading ----------------------------- */

    /**
     * The document an order became — its number and date, whichever table
     * that happens to be in (the bill-type vocabulary is shared with the
     * accounting bill-wise details).
     *
     * @return array<string, mixed>|null
     */
    protected function document(Order $order): ?array
    {
        if (! $order->document_type || ! $order->document_id) {
            return null;
        }

        $row = match ($order->document_type) {
            'invoice' => DB::table('invoices')
                ->select('id', 'invoice_number as number', 'issue_date as date')
                ->where('id', $order->document_id)->first(),
            'purchase-order' => DB::table('purchase_orders')
                ->select('id', 'po_number as number', 'created_at as date')
                ->where('id', $order->document_id)->first(),
            default => DB::table('vouchers')
                ->select('id', 'voucher_no as number', 'voucher_date as date')
                ->where('id', $order->document_id)->first(),
        };

        if ($row === null) {
            return null;
        }

        return [
            'type' => $order->document_type,
            'id' => (int) $row->id,
            'number' => $row->number,
            'date' => is_string($row->date) ? substr($row->date, 0, 10) : $row->date,
        ];
    }

    /**
     * What is still owed on the linked bill: due date, paid, remaining,
     * ageing bucket and the overdue flag, plus the party's own outstanding
     * total. Straight from the Outstanding report, so the order can never
     * claim something the books do not. `bill` is null once the document is
     * fully settled (settled bills are not outstanding, by definition).
     *
     * @return array<string, mixed>|null
     */
    protected function payment(Order $order): ?array
    {
        if (! $order->document_type || ! $order->document_id) {
            return null;
        }

        $ledger = $order->side === 'sales'
            ? $order->customer?->ledger
            : $order->supplier?->ledger;

        if ($ledger === null) {
            return ['bill' => null, 'party_outstanding' => null];
        }

        $side = $order->side === 'sales' ? 'receivable' : 'payable';

        $report = $this->reports->outstanding([
            'side' => $side,
            'ledger_id' => $ledger->id,
            'as_of' => now()->toDateString(),
        ]);

        $bill = collect($report['bills'])->first(
            fn (array $b): bool => $b['bill_type'] === $order->document_type
                && (int) $b['bill_id'] === (int) $order->document_id
        );

        return [
            'bill' => $bill,
            'party_outstanding' => $report['totals'][$side] ?? null,
        ];
    }
}
