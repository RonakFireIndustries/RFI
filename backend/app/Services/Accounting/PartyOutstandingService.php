<?php

namespace App\Services\Accounting;

use App\Models\LedgerAccount;
use App\Models\VoucherBillAllocation;
use Illuminate\Support\Facades\Schema;

/**
 * Works out what a party still owes (Sundry Debtor) or is owed (Sundry
 * Creditor), and how much of each bill is already settled.
 *
 * Sources, in the books' own terms:
 *   1. The ledger's opening balance (brought forward into the year).
 *   2. Legacy documents still in the database — invoices for customers,
 *      purchase orders for suppliers. Their own `paid_amount` / payments are
 *      treated as already-settled so the old and new worlds don't double count.
 *   3. Bill-wise allocations recorded by posted vouchers (payments, receipts,
 *      journals, and later the sales / purchase notes).
 *
 * Legacy tables are probed defensively: the accounting SQLite test suite does
 * not build them, and they are retired in a later slice, so their absence must
 * never break the endpoint.
 */
class PartyOutstandingService
{
    /**
     * @return array{
     *     ledger: array<string, mixed>,
     *     bills: array<int, array<string, mixed>>,
     *     total_outstanding: float,
     *     total_advance: float
     * }
     */
    public function forLedger(LedgerAccount $ledger): array
    {
        $allocations = $this->settledAmounts($ledger->id);
        $advances = $this->advanceTotal($ledger->id);

        $bills = [];

        $this->appendOpeningBill($ledger, $allocations, $bills);
        $this->appendInvoiceBills($ledger, $allocations, $bills);
        $this->appendPurchaseOrderBills($ledger, $allocations, $bills);
        $this->appendVoucherBills($ledger, $allocations, $bills);

        $bills = array_values(array_filter($bills, static fn (array $bill): bool => abs($bill['balance']) >= 0.005));

        $totalOutstanding = round(array_sum(array_map(
            static fn (array $bill): float => $bill['balance'],
            $bills
        )), 2);

        return [
            'ledger' => [
                'id' => $ledger->id,
                'name' => $ledger->name,
                'party_type' => $ledger->party_type,
                'party_id' => $ledger->party_id,
                'is_bank' => (bool) $ledger->is_bank,
                'is_cash' => (bool) $ledger->is_cash,
            ],
            'bills' => $bills,
            'total_outstanding' => $totalOutstanding,
            'total_advance' => $advances,
        ];
    }

    /**
     * Total already settled per "bill_type:bill_id" across posted vouchers.
     *
     * @return array<string, float>
     */
    protected function settledAmounts(int $ledgerId): array
    {
        return VoucherBillAllocation::query()
            ->where('ledger_id', $ledgerId)
            ->whereHas('voucher', fn ($q) => $q->where('status', 'posted'))
            ->get(['bill_type', 'bill_id', 'amount'])
            ->groupBy(fn (VoucherBillAllocation $a) => $a->bill_type . ':' . ($a->bill_id ?? ''))
            ->map(fn ($rows) => round((float) $rows->sum('amount'), 2))
            ->all();
    }

    /** Advances / on-account amounts held against a party. */
    protected function advanceTotal(int $ledgerId): float
    {
        return round((float) VoucherBillAllocation::query()
            ->where('ledger_id', $ledgerId)
            ->whereIn('bill_type', ['advance', 'on-account'])
            ->whereHas('voucher', fn ($q) => $q->where('status', 'posted'))
            ->sum('amount'), 2);
    }

    protected function appendOpeningBill(LedgerAccount $ledger, array $allocations, array &$bills): void
    {
        $opening = round((float) $ledger->opening_balance, 2);
        if ($opening <= 0) {
            return;
        }

        $settled = $allocations['opening:'] ?? 0.0;

        $bills[] = [
            'bill_type' => 'opening',
            'bill_id' => null,
            'bill_name' => 'Opening Balance',
            'date' => null,
            'due_date' => null,
            'total' => $opening,
            'settled' => $settled,
            'balance' => round($opening - $settled, 2),
            'side' => $ledger->opening_type ?: 'Dr',
        ];
    }

    protected function appendInvoiceBills(LedgerAccount $ledger, array $allocations, array &$bills): void
    {
        if ($ledger->party_type !== 'customer' || ! $ledger->party_id) {
            return;
        }
        if (! Schema::hasTable('invoices') || ! Schema::hasColumn('invoices', 'customer_id')) {
            return;
        }

        $columns = ['id', 'invoice_number', 'status', 'grand_total', 'total_amount'];
        foreach (['issue_date', 'due_date', 'paid_amount'] as $optional) {
            if (Schema::hasColumn('invoices', $optional)) {
                $columns[] = $optional;
            }
        }

        $invoices = \Illuminate\Support\Facades\DB::table('invoices')
            ->where('customer_id', $ledger->party_id)
            ->whereNotIn('status', ['Cancelled', 'cancelled'])
            ->orderBy('id')
            ->get($columns);

        foreach ($invoices as $invoice) {
            $total = round((float) ($invoice->grand_total ?: $invoice->total_amount), 2);
            $legacyPaid = round((float) ($invoice->paid_amount ?? 0), 2);
            $settled = round($legacyPaid + ($allocations['invoice:' . $invoice->id] ?? 0), 2);

            $bills[] = [
                'bill_type' => 'invoice',
                'bill_id' => (int) $invoice->id,
                'bill_name' => $invoice->invoice_number,
                'date' => $invoice->issue_date ?? null,
                'due_date' => $invoice->due_date ?? null,
                'total' => $total,
                'settled' => $settled,
                'balance' => round($total - $settled, 2),
                'side' => 'Dr',
                'status' => $invoice->status,
            ];
        }
    }

    protected function appendPurchaseOrderBills(LedgerAccount $ledger, array $allocations, array &$bills): void
    {
        if ($ledger->party_type !== 'supplier' || ! $ledger->party_id) {
            return;
        }
        if (! Schema::hasTable('purchase_orders') || ! Schema::hasColumn('purchase_orders', 'supplier_id')) {
            return;
        }

        $columns = ['id', 'po_number', 'status', 'total_amount'];
        foreach (['created_at'] as $optional) {
            if (Schema::hasColumn('purchase_orders', $optional)) {
                $columns[] = $optional;
            }
        }

        $orders = \Illuminate\Support\Facades\DB::table('purchase_orders')
            ->where('supplier_id', $ledger->party_id)
            ->whereNotIn('status', ['Cancelled', 'cancelled'])
            ->orderBy('id')
            ->get($columns);

        $legacyPayments = $this->legacyPaymentsByPurchaseOrder();

        foreach ($orders as $order) {
            $total = round((float) $order->total_amount, 2);
            $legacyPaid = round((float) ($legacyPayments[$order->id] ?? 0), 2);
            $settled = round($legacyPaid + ($allocations['purchase-order:' . $order->id] ?? 0), 2);

            $bills[] = [
                'bill_type' => 'purchase-order',
                'bill_id' => (int) $order->id,
                'bill_name' => $order->po_number,
                'date' => $order->created_at ?? null,
                'due_date' => null,
                'total' => $total,
                'settled' => $settled,
                'balance' => round($total - $settled, 2),
                'side' => 'Cr',
                'status' => $order->status,
            ];
        }
    }

    protected function appendVoucherBills(LedgerAccount $ledger, array $allocations, array &$bills): void
    {
        if (! Schema::hasTable('vouchers')) {
            return;
        }
        if ($ledger->party_type === 'customer') {
            $vouchers = \App\Models\Voucher::where('voucher_type', 'sales')
                ->where('party_ledger_id', $ledger->id)
                ->where('status', 'posted')
                ->orderBy('voucher_date')->orderBy('id')
                ->get(['id', 'voucher_no', 'voucher_date', 'amount']);
            foreach ($vouchers as $v) {
                $settled = $allocations['sales-voucher:' . $v->id] ?? 0.0;
                $bills[] = [
                    'bill_type' => 'sales-voucher',
                    'bill_id' => $v->id,
                    'bill_name' => $v->voucher_no,
                    'date' => $v->voucher_date,
                    'due_date' => null,
                    'total' => round((float) $v->amount, 2),
                    'settled' => round($settled, 2),
                    'balance' => round((float) $v->amount - $settled, 2),
                    'side' => 'Dr',
                ];
            }
        }
        if ($ledger->party_type === 'supplier') {
            $vouchers = \App\Models\Voucher::where('voucher_type', 'purchase')
                ->where('party_ledger_id', $ledger->id)
                ->where('status', 'posted')
                ->orderBy('voucher_date')->orderBy('id')
                ->get(['id', 'voucher_no', 'voucher_date', 'amount']);
            foreach ($vouchers as $v) {
                $settled = $allocations['purchase-voucher:' . $v->id] ?? 0.0;
                $bills[] = [
                    'bill_type' => 'purchase-voucher',
                    'bill_id' => $v->id,
                    'bill_name' => $v->voucher_no,
                    'date' => $v->voucher_date,
                    'due_date' => null,
                    'total' => round((float) $v->amount, 2),
                    'settled' => round($settled, 2),
                    'balance' => round((float) $v->amount - $settled, 2),
                    'side' => 'Cr',
                ];
            }
        }
    }

    /**
     * Legacy payments recorded against purchase orders.
     *
     * @return array<int, float>
     */
    protected function legacyPaymentsByPurchaseOrder(): array
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'purchase_order_id')) {
            return [];
        }

        return \Illuminate\Support\Facades\DB::table('payments')
            ->whereNotNull('purchase_order_id')
            ->groupBy('purchase_order_id')
            ->pluck(\Illuminate\Support\Facades\DB::raw('SUM(amount)'), 'purchase_order_id')
            ->map(fn ($amount) => (float) $amount)
            ->all();
    }
}
