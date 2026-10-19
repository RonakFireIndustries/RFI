<?php

namespace App\Services\Accounting;

use App\Models\AccountGroup;
use App\Models\Customer;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * One-off move of the legacy sales and purchase documents into the books.
 *
 * Invoices become `sales` vouchers and purchase orders become `purchase`
 * vouchers, each carrying its line items, its GST and the original document
 * number as `reference_no`. The document row keeps a pointer to the voucher it
 * became (`accounting_voucher_id`), which makes the migration idempotent and
 * stops Outstandings counting the same bill in both worlds.
 *
 * The legacy books already treated part of these documents as settled —
 * `invoices.paid_amount` for customers, the `payments` rows for suppliers. That
 * money never had a ledger of its own, so it cannot be replayed as a receipt
 * without inventing which bank or cash account took it. Instead each party gets
 * one journal that credits (customer) or debits (supplier) the party for the
 * settled total, parks the other side in a clearly-labelled `Migration
 * Suspense` ledger, and carries bill-wise details pointing at the new vouchers.
 * Outstanding is therefore what it was the day before, the party ledger balance
 * and the bill-wise balance agree, and the accountant reclassifies the suspense
 * into the real receipt / payment accounts afterwards.
 *
 * Documents are taken whole, whatever their workflow status, so the set of
 * bills matches the set Outstandings counted before the migration.
 *
 * Every legacy table is probed defensively, as in PartyOutstandingService: the
 * accounting SQLite suite does not build them.
 */
class LegacyDocumentMigrator
{
    /** Reference stamped on every generated settlement journal. */
    public const SETTLEMENT_REFERENCE = 'LEGACY-SETTLEMENT';

    protected const SUSPENSE_LEDGER = 'Migration Suspense';
    protected const SUSPENSE_GROUP = 'Suspense Account';

    /**
     * A header total that disagrees with the sum of its lines by more than this
     * is a data problem to report, not rounding.
     */
    protected const RESIDUAL_TOLERANCE = 1.0;

    public function __construct(
        protected ItemVoucherService $itemVouchers,
        protected VoucherService $vouchers,
        protected PartyLedgerService $partyLedgers,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function migrate(bool $dryRun = false): array
    {
        $this->assertSchemaReady();

        $summary = [
            'dry_run' => $dryRun,
            'suspense_ledger' => self::SUSPENSE_LEDGER,
            'invoice' => ['migrated' => 0, 'skipped' => 0, 'settled' => 0.0, 'errors' => []],
            'purchase-order' => ['migrated' => 0, 'skipped' => 0, 'settled' => 0.0, 'errors' => []],
            'settlement' => ['journals' => 0, 'amount' => 0.0, 'clamped' => 0, 'errors' => []],
            'residual' => ['count' => 0, 'amount' => 0.0],
            'shipping' => ['count' => 0, 'amount' => 0.0],
            'statuses' => ['invoice' => [], 'purchase-order' => []],
        ];

        $settlements = [];
        $userId = $dryRun ? 0 : $this->systemUserId();

        // Resolve the suspense ledger before writing anything, so a missing
        // chart of accounts fails the whole run rather than half of it.
        if (! $dryRun) {
            $this->suspenseLedger();
        }

        $this->migrateInvoices($dryRun, $userId, $summary, $settlements);
        $this->migratePurchaseOrders($dryRun, $userId, $summary, $settlements);

        if ($dryRun) {
            $summary['settlement']['amount'] = round(array_sum(array_map(
                static fn (array $bucket): float => array_sum(array_column($bucket['items'], 'amount')),
                $settlements
            )), 2);
        } else {
            $this->postSettlements($userId, $settlements, $summary);
        }

        foreach (['invoice', 'purchase-order'] as $type) {
            $summary[$type]['settled'] = round($summary[$type]['settled'], 2);
        }
        $summary['residual']['amount'] = round($summary['residual']['amount'], 2);
        $summary['shipping']['amount'] = round($summary['shipping']['amount'], 2);

        return $summary;
    }

    /**
     * Undo a previous run: delete the vouchers it created and unlink the
     * documents. Bill-wise details that were re-pointed at the new vouchers are
     * removed with them, so the legacy bills come back exactly as they were.
     *
     * @return array<string, mixed>
     */
    public function rollback(): array
    {
        $this->assertSchemaReady();

        $summary = ['invoice' => 0, 'purchase-order' => 0, 'settlement' => 0, 'errors' => []];

        foreach (['invoices' => 'invoice', 'purchase_orders' => 'purchase-order'] as $table => $label) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'accounting_voucher_id')) {
                continue;
            }

            $voucherIds = DB::table($table)->whereNotNull('accounting_voucher_id')->pluck('accounting_voucher_id')->all();

            foreach ($voucherIds as $voucherId) {
                try {
                    $voucher = Voucher::find($voucherId);
                    if ($voucher) {
                        $this->itemVouchers->delete($voucher);
                    }
                    $summary[$label]++;
                } catch (\Throwable $e) {
                    $summary['errors'][] = "{$table} voucher #{$voucherId}: {$e->getMessage()}";
                }
            }

            DB::table($table)->whereNotNull('accounting_voucher_id')->update(['accounting_voucher_id' => null]);
        }

        // Settlement journals are found by their reference; no document points
        // at them.
        foreach (Voucher::where('reference_no', self::SETTLEMENT_REFERENCE)->get() as $journal) {
            try {
                $this->vouchers->delete($journal);
                $summary['settlement']++;
            } catch (\Throwable $e) {
                $summary['errors'][] = "settlement voucher #{$journal->id}: {$e->getMessage()}";
            }
        }

        return $summary;
    }

    /* ------------------------------- invoices ------------------------------ */

    protected function migrateInvoices(bool $dryRun, int $userId, array &$summary, array &$settlements): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        $summary['invoice']['skipped'] = (int) DB::table('invoices')->whereNotNull('accounting_voucher_id')->count();

        $invoices = DB::table('invoices')
            ->whereNull('accounting_voucher_id')
            ->whereNotIn('status', ['Cancelled', 'cancelled'])
            ->orderBy('id')
            ->get();

        foreach ($invoices as $invoice) {
            $status = (string) ($invoice->status ?? 'Unknown');
            $summary['statuses']['invoice'][$status] = ($summary['statuses']['invoice'][$status] ?? 0) + 1;

            try {
                $plan = $this->planInvoice($invoice, $dryRun);
            } catch (\Throwable $e) {
                $summary['invoice']['errors'][] = $this->label('#', $invoice->id, $invoice->invoice_number ?? null, $e);
                continue;
            }

            if ($dryRun) {
                $voucher = null;
            } else {
                try {
                    $voucher = $this->itemVouchers->create($plan['payload'], $userId);
                } catch (\Throwable $e) {
                    $summary['invoice']['errors'][] = $this->label('#', $invoice->id, $invoice->invoice_number ?? null, $e);
                    continue;
                }

                DB::table('invoices')->where('id', $invoice->id)->update(['accounting_voucher_id' => $voucher->id]);
                $this->retypeAllocations('invoice', (int) $invoice->id, 'sales-voucher', (int) $voucher->id);
            }

            $this->tally($summary, 'invoice', $plan);

            $this->recordSettlement(
                $settlements,
                'customer',
                $plan['party_key'],
                $plan['ledger_id'],
                'sales-voucher',
                (int) ($voucher?->id ?? 0),
                (string) ($voucher?->voucher_no ?? $plan['reference_no']),
                $plan['settled'],
                $plan['date']
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function planInvoice(object $invoice, bool $dryRun): array
    {
        $reference = (string) (($invoice->invoice_number ?? '') !== '' ? $invoice->invoice_number : ('#' . $invoice->id));

        if (! $invoice->customer_id) {
            throw new RuntimeException('the invoice has no customer to post against.');
        }

        $customer = Customer::find($invoice->customer_id);
        if (! $customer) {
            throw new RuntimeException('customer #' . $invoice->customer_id . ' no longer exists.');
        }

        $subtotal = round((float) ($invoice->subtotal ?? 0), 2);
        $igst = round((float) ($invoice->igst_total ?? 0), 2);
        $taxTotal = round(
            (float) ($invoice->cgst_total ?? 0) + (float) ($invoice->sgst_total ?? 0) + $igst,
            2
        );

        $total = round((float) (($invoice->grand_total ?? 0) ?: ($invoice->total_amount ?? 0)), 2);
        if ($total <= 0) {
            throw new RuntimeException('the invoice has no value.');
        }

        // Legacy GST was recorded per state, the engine wants intra/inter.
        $gstType = ($igst > 0 || ($invoice->gst_type ?? null) === 'igst') ? 'inter' : 'intra';
        $taxRate = $this->effectiveTaxRate((float) ($invoice->gst_rate ?? 0), $taxTotal, $subtotal);
        $date = $this->dateOf($invoice->issue_date ?? null, $invoice->created_at ?? null);

        $items = $this->invoiceItems($invoice, $taxRate, $subtotal > 0 ? $subtotal : round($total - $taxTotal, 2));
        $projected = $this->projectTotals($items, $gstType);

        // Whatever the line maths leaves over is folded in as round off, so the
        // migrated voucher is worth exactly what the legacy document was.
        $roundOff = round($total - $projected['taxable_total'] - $projected['tax_total'], 2);
        $paid = round((float) ($invoice->paid_amount ?? 0), 2);

        $ledger = $dryRun ? $this->partyLedgers->for($customer) : $this->partyLedgers->ensure($customer);

        return [
            'party_key' => 'customer:' . $customer->getKey(),
            'ledger_id' => $ledger?->id,
            'date' => $date,
            'reference_no' => $reference,
            'settled' => round(min($paid, $total), 2),
            'clamped' => $paid - $total >= 0.01,
            'shipping' => 0.0,
            'round_off' => $roundOff,
            'residual_exceeds' => abs($roundOff) > self::RESIDUAL_TOLERANCE,
            'payload' => [
                'voucher_type' => 'sales',
                'voucher_date' => $date,
                // Keep the legacy invoice's terms, so a bill that was due on
                // 31 May before the migration is still due on 31 May after it.
                'due_date' => $invoice->due_date ?? null,
                'reference_no' => $reference,
                'narration' => 'Migrated from invoice ' . $reference . '.',
                'party_ledger_id' => $ledger?->id ?? 0,
                'gst_type' => $gstType,
                'items' => $items,
                'round_off' => $roundOff,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function invoiceItems(object $invoice, float $taxRate, float $fallbackRate): array
    {
        if (! Schema::hasTable('invoice_items')) {
            return [$this->fallbackItem('Invoice ' . ($invoice->invoice_number ?? $invoice->id), $fallbackRate, $taxRate)];
        }

        $rows = DB::table('invoice_items')->where('invoice_id', $invoice->id)->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return [$this->fallbackItem('Invoice ' . ($invoice->invoice_number ?? $invoice->id), $fallbackRate, $taxRate)];
        }

        $items = [];
        foreach ($rows as $row) {
            $quantity = (float) $row->quantity;
            $rate = round((float) $row->unit_price, 2);
            $gross = round($quantity * $rate, 2);
            $discount = round((float) ($row->discount ?? 0), 2);

            $items[] = [
                'product_id' => $row->product_id ?: null,
                'item_description' => (string) ((($row->item_description ?? '') !== '') ? $row->item_description : 'Item'),
                'hsn_code' => $row->hsn_code ?? null,
                'quantity' => $quantity,
                'rate' => $rate,
                // Legacy stored the discount as an amount; the engine wants a
                // percentage. The conversion rounds to 2dp, so paise can drift
                // by a cent or two — the round off line absorbs it.
                'discount_percent' => $gross > 0 ? round($discount / $gross * 100, 2) : 0.0,
                'tax_rate' => $taxRate,
            ];
        }

        return $items;
    }

    /* ---------------------------- purchase orders -------------------------- */

    protected function migratePurchaseOrders(bool $dryRun, int $userId, array &$summary, array &$settlements): void
    {
        if (! Schema::hasTable('purchase_orders')) {
            return;
        }

        $summary['purchase-order']['skipped'] = (int) DB::table('purchase_orders')->whereNotNull('accounting_voucher_id')->count();

        $orders = DB::table('purchase_orders')
            ->whereNull('accounting_voucher_id')
            ->whereNotIn('status', ['Cancelled', 'cancelled'])
            ->orderBy('id')
            ->get();

        $payments = $this->legacyPayments();

        foreach ($orders as $order) {
            $status = (string) ($order->status ?? 'Unknown');
            $summary['statuses']['purchase-order'][$status] = ($summary['statuses']['purchase-order'][$status] ?? 0) + 1;

            try {
                $plan = $this->planPurchaseOrder($order, $payments, $dryRun);
            } catch (\Throwable $e) {
                $summary['purchase-order']['errors'][] = $this->label('PO #', $order->id, $order->po_number ?? null, $e);
                continue;
            }

            if ($dryRun) {
                $voucher = null;
            } else {
                try {
                    $voucher = $this->itemVouchers->create($plan['payload'], $userId);
                } catch (\Throwable $e) {
                    $summary['purchase-order']['errors'][] = $this->label('PO #', $order->id, $order->po_number ?? null, $e);
                    continue;
                }

                DB::table('purchase_orders')->where('id', $order->id)->update(['accounting_voucher_id' => $voucher->id]);
                $this->retypeAllocations('purchase-order', (int) $order->id, 'purchase-voucher', (int) $voucher->id);
            }

            $this->tally($summary, 'purchase-order', $plan);

            $this->recordSettlement(
                $settlements,
                'supplier',
                $plan['party_key'],
                $plan['ledger_id'],
                'purchase-voucher',
                (int) ($voucher?->id ?? 0),
                (string) ($voucher?->voucher_no ?? $plan['reference_no']),
                $plan['settled'],
                $plan['date']
            );
        }
    }

    /**
     * @param  array<int, float>  $payments
     * @return array<string, mixed>
     */
    protected function planPurchaseOrder(object $order, array $payments, bool $dryRun): array
    {
        $reference = (string) ((($order->po_number ?? '') !== '') ? $order->po_number : ('#' . $order->id));

        if (! $order->supplier_id) {
            throw new RuntimeException('the purchase order has no supplier to post against.');
        }

        $supplier = Supplier::find($order->supplier_id);
        if (! $supplier) {
            throw new RuntimeException('supplier #' . $order->supplier_id . ' no longer exists.');
        }

        // `total_amount` holds the pre-tax subtotal and `tax_amount` the input
        // GST; together they are what the supplier billed, which is the
        // liability the books should carry.
        $subtotal = round((float) ($order->total_amount ?? 0), 2);
        $taxTotal = round((float) ($order->tax_amount ?? 0), 2);
        $total = round($subtotal + $taxTotal, 2);

        if ($total <= 0) {
            throw new RuntimeException('the purchase order has no value.');
        }

        $gstType = ($order->gst_type ?? null) === 'igst' ? 'inter' : 'intra';
        $taxRate = $this->effectiveTaxRate((float) ($order->gst_rate ?? 0), $taxTotal, $subtotal);
        $date = $this->dateOf($order->created_at ?? null);

        $items = $this->purchaseOrderItems($order, $taxRate, $subtotal);
        $projected = $this->projectTotals($items, $gstType);
        $roundOff = round($total - $projected['taxable_total'] - $projected['tax_total'], 2);

        $shipping = round((float) ($order->shipping_cost ?? 0) + (float) ($order->other_cost ?? 0), 2);
        $paid = round((float) ($payments[$order->id] ?? 0), 2);

        $ledger = $dryRun ? $this->partyLedgers->for($supplier) : $this->partyLedgers->ensure($supplier);

        return [
            'party_key' => 'supplier:' . $supplier->getKey(),
            'ledger_id' => $ledger?->id,
            'date' => $date,
            'reference_no' => $reference,
            'settled' => round(min($paid, $total), 2),
            'clamped' => $paid - $total >= 0.01,
            'shipping' => $shipping,
            'round_off' => $roundOff,
            'residual_exceeds' => abs($roundOff) > self::RESIDUAL_TOLERANCE,
            'payload' => [
                'voucher_type' => 'purchase',
                'voucher_date' => $date,
                'reference_no' => $reference,
                'narration' => 'Migrated from purchase order ' . $reference . '.',
                'party_ledger_id' => $ledger?->id ?? 0,
                'gst_type' => $gstType,
                'items' => $items,
                'round_off' => $roundOff,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function purchaseOrderItems(object $order, float $fallbackTaxRate, float $fallbackRate): array
    {
        if (! Schema::hasTable('purchase_order_items')) {
            return [$this->fallbackItem('Purchase order ' . ($order->po_number ?? $order->id), $fallbackRate, $fallbackTaxRate)];
        }

        $rows = DB::table('purchase_order_items')->where('purchase_order_id', $order->id)->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return [$this->fallbackItem('Purchase order ' . ($order->po_number ?? $order->id), $fallbackRate, $fallbackTaxRate)];
        }

        $names = $this->productNames($rows->pluck('product_id')->all());

        // The legacy tax is the sum of the per-line rates, so a line keeps its
        // own rate. Only when no line carries one is the header's effective
        // rate used, otherwise the tax would be dropped entirely.
        $anyRated = $rows->contains(static fn ($row): bool => (float) ($row->gst_rate ?? 0) > 0);

        $items = [];
        foreach ($rows as $row) {
            $rate = round((float) $row->unit_cost, 2);
            $lineRate = round((float) ($row->gst_rate ?? 0), 2);

            if (! $anyRated) {
                $lineRate = $fallbackTaxRate;
            }

            $items[] = [
                'product_id' => $row->product_id ?: null,
                'item_description' => (string) ((($row->custom_product_name ?? '') !== '')
                    ? $row->custom_product_name
                    : ($names[$row->product_id] ?? 'Item')),
                'hsn_code' => $row->hsn_code ?? null,
                'quantity' => (float) $row->quantity,
                'rate' => $rate,
                'discount_percent' => 0.0,
                'tax_rate' => $lineRate,
            ];
        }

        return $items;
    }

    /* ------------------------------ settlements ---------------------------- */

    protected function recordSettlement(
        array &$settlements,
        string $type,
        string $partyKey,
        ?int $ledgerId,
        string $billType,
        int $billId,
        string $billName,
        float $amount,
        string $date
    ): void {
        if ($amount <= 0) {
            return;
        }

        $settlements[$partyKey] ??= ['type' => $type, 'ledger_id' => $ledgerId, 'items' => []];

        if ($ledgerId && ! $settlements[$partyKey]['ledger_id']) {
            $settlements[$partyKey]['ledger_id'] = $ledgerId;
        }

        $settlements[$partyKey]['items'][] = [
            'bill_type' => $billType,
            'bill_id' => $billId,
            'bill_name' => $billName,
            'amount' => round($amount, 2),
            'date' => $date,
        ];
    }

    /**
     * One journal per party carries every settled amount that never had a
     * ledger. The suspense ledger is the honest placeholder: the money really
     * did move, but the legacy data does not say where to.
     */
    protected function postSettlements(int $userId, array $settlements, array &$summary): void
    {
        $suspense = $this->suspenseLedger();

        foreach ($settlements as $partyKey => $bucket) {
            $ledgerId = (int) ($bucket['ledger_id'] ?? 0);
            $items = array_values(array_filter($bucket['items'], static fn (array $item): bool => $item['amount'] > 0));

            if ($ledgerId === 0 || $items === []) {
                continue;
            }

            $amount = round(array_sum(array_column($items, 'amount')), 2);
            if ($amount <= 0) {
                continue;
            }

            $customer = $bucket['type'] === 'customer';

            try {
                $this->vouchers->create([
                    'voucher_type' => 'journal',
                    'voucher_date' => $this->latestDate(array_column($items, 'date')),
                    'reference_no' => self::SETTLEMENT_REFERENCE,
                    'narration' => 'Settled portion of legacy documents migrated into the books. Reclassify '
                        . self::SUSPENSE_LEDGER . ' once the receipt / payment account is known.',
                    'party_ledger_id' => $ledgerId,
                    'entries' => [
                        [
                            'ledger_id' => $suspense->id,
                            'entry_type' => $customer ? 'Dr' : 'Cr',
                            'amount' => $amount,
                        ],
                        [
                            'ledger_id' => $ledgerId,
                            'entry_type' => $customer ? 'Cr' : 'Dr',
                            'amount' => $amount,
                        ],
                    ],
                    'bill_allocations' => array_map(static fn (array $item): array => [
                        'bill_type' => $item['bill_type'],
                        'bill_id' => $item['bill_id'],
                        'bill_name' => $item['bill_name'],
                        'amount' => $item['amount'],
                    ], $items),
                ], $userId);

                $summary['settlement']['journals']++;
                $summary['settlement']['amount'] = round($summary['settlement']['amount'] + $amount, 2);
            } catch (\Throwable $e) {
                $summary['settlement']['errors'][] = "{$partyKey}: {$e->getMessage()}";
            }
        }
    }

    /* -------------------------------- helpers ------------------------------ */

    /**
     * Roll the per-document oddities into the run summary.
     */
    protected function tally(array &$summary, string $type, array $plan): void
    {
        $summary[$type]['migrated']++;
        $summary[$type]['settled'] += $plan['settled'];

        if ($plan['clamped']) {
            $summary['settlement']['clamped']++;
        }

        if ($plan['residual_exceeds']) {
            $summary['residual']['count']++;
            $summary['residual']['amount'] += $plan['round_off'];
        }

        if ($plan['shipping'] > 0) {
            $summary['shipping']['count']++;
            $summary['shipping']['amount'] += $plan['shipping'];
        }
    }

    /**
     * Mirrors ItemVoucherService::normaliseItems closely enough to predict the
     * totals it will compute, which is what lets the document total be held
     * exact through the round off line.
     *
     * @return array{taxable_total: float, discount_total: float, tax_total: float}
     */
    protected function projectTotals(array $items, string $gstType): array
    {
        $taxable = 0.0;
        $discount = 0.0;
        $tax = 0.0;

        foreach ($items as $item) {
            $qty = round((float) $item['quantity'], 3);
            $rate = round((float) $item['rate'], 2);
            $discPct = round((float) ($item['discount_percent'] ?? 0), 2);
            $taxRate = round((float) ($item['tax_rate'] ?? 0), 2);

            $gross = round($qty * $rate, 2);
            $lineDiscount = round($gross * $discPct / 100, 2);
            $lineTaxable = round($gross - $lineDiscount, 2);

            $taxable += $lineTaxable;
            $discount += $lineDiscount;
            $tax += round($lineTaxable * $taxRate / 100, 2);
        }

        return [
            'taxable_total' => round($taxable, 2),
            'discount_total' => round($discount, 2),
            'tax_total' => round($tax, 2),
        ];
    }

    /**
     * An invoice / purchase order with no lines still has to become a voucher,
     * so the header total is expressed as a single line.
     *
     * @return array<string, mixed>
     */
    protected function fallbackItem(string $label, float $rate, float $taxRate): array
    {
        return [
            'product_id' => null,
            'item_description' => $label,
            'hsn_code' => null,
            'quantity' => 1,
            'rate' => round($rate, 2),
            'discount_percent' => 0.0,
            'tax_rate' => round($taxRate, 2),
        ];
    }

    protected function effectiveTaxRate(float $rate, float $taxTotal, float $taxable): float
    {
        if ($rate > 0) {
            return round($rate, 2);
        }

        if ($taxTotal > 0 && $taxable > 0) {
            return round($taxTotal / $taxable * 100, 2);
        }

        return 0.0;
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, string>
     */
    protected function productNames(array $ids): array
    {
        $ids = array_values(array_filter(array_unique($ids)));

        if ($ids === [] || ! Schema::hasTable('products')) {
            return [];
        }

        return DB::table('products')->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    /**
     * Anything already recorded against a legacy bill — a receipt or payment
     * entered before the migration — has to follow the document into its
     * voucher, or the settlement would be orphaned.
     */
    protected function retypeAllocations(string $fromType, int $billId, string $toType, int $voucherId): void
    {
        if (! Schema::hasTable('voucher_bill_allocations')) {
            return;
        }

        DB::table('voucher_bill_allocations')
            ->where('bill_type', $fromType)
            ->where('bill_id', $billId)
            ->update(['bill_type' => $toType, 'bill_id' => $voucherId]);
    }

    /**
     * Legacy payments already recorded against purchase orders.
     *
     * @return array<int, float>
     */
    protected function legacyPayments(): array
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'purchase_order_id')) {
            return [];
        }

        return DB::table('payments')
            ->whereNotNull('purchase_order_id')
            ->groupBy('purchase_order_id')
            ->pluck(DB::raw('SUM(amount)'), 'purchase_order_id')
            ->map(static fn ($amount): float => (float) $amount)
            ->all();
    }

    protected function suspenseLedger(): LedgerAccount
    {
        $existing = LedgerAccount::findByName(self::SUSPENSE_LEDGER);
        if ($existing) {
            return $existing;
        }

        $group = AccountGroup::where('name', self::SUSPENSE_GROUP)->orderBy('id')->first();
        if (! $group) {
            throw new RuntimeException(
                'The "' . self::SUSPENSE_GROUP . '" group is missing. Run `php artisan db:seed --class=ChartOfAccountsSeeder` first.'
            );
        }

        return LedgerAccount::create([
            'name' => self::SUSPENSE_LEDGER,
            'group_id' => $group->id,
            'status' => 'active',
            'notes' => 'Holds the settled part of legacy documents until it is reclassified to the real receipt / payment account.',
        ]);
    }

    protected function assertSchemaReady(): void
    {
        foreach (['invoices', 'purchase_orders'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'accounting_voucher_id')) {
                throw new RuntimeException("`{$table}` has no accounting_voucher_id column — run `php artisan migrate` first.");
            }
        }
    }

    protected function systemUserId(): int
    {
        $id = DB::table('users')->orderBy('id')->value('id');

        if (! $id) {
            throw new RuntimeException('No user account exists to attribute the migrated vouchers to.');
        }

        return (int) $id;
    }

    protected function dateOf(mixed ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            if ($candidate) {
                return Carbon::parse($candidate)->toDateString();
            }
        }

        return now()->toDateString();
    }

    /**
     * @param  array<int, mixed>  $dates
     */
    protected function latestDate(array $dates): string
    {
        $dates = array_values(array_filter($dates));

        if ($dates === []) {
            return now()->toDateString();
        }

        sort($dates);

        return (string) end($dates);
    }

    protected function label(string $prefix, mixed $id, ?string $reference, \Throwable $e): string
    {
        $reference = ($reference ?? '') !== '' ? " ({$reference})" : '';

        return "{$prefix}{$id}{$reference}: {$e->getMessage()}";
    }
}
