<?php

namespace App\Services\Accounting;

use App\Models\Customer;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ItemVoucherService
{
    public function __construct(protected VoucherService $vouchers)
    {
    }

    public function create(array $data, int $userId): Voucher
    {
        return DB::transaction(function () use ($data, $userId) {
            $type = $this->assertItemType($data['voucher_type']);
            $built = $this->build($type, $data);
            $voucher = $this->vouchers->create($built['header'], $userId);
            $this->saveItems($voucher, $built['items']);
            $voucher->update([
                // `amount` is the document value (what the party owes/is owed),
                // which with round off differs from the sum of debits.
                'amount' => $built['totals']['grand_total'],
                'taxable_total' => $built['totals']['taxable_total'],
                'discount_total' => $built['totals']['discount_total'],
                'discount_type' => $built['totals']['discount_type'],
                'discount_value' => $built['totals']['discount_value'],
                'tax_rate' => $built['totals']['tax_rate'],
                'tax_total' => $built['totals']['tax_total'],
                'round_off' => $built['totals']['round_off'],
                'gst_type' => $built['totals']['gst_type'],
                // Terms belong to the document rather than the double entry:
                // only item vouchers (which are bills) carry a due date.
                'due_date' => $built['due_date'],
            ]);
            return $voucher->fresh()->load($this->relations());
        });
    }

    public function update(Voucher $voucher, array $data, int $userId): Voucher
    {
        return DB::transaction(function () use ($voucher, $data, $userId) {
            $type = $this->assertItemType($data['voucher_type'] ?? $voucher->voucher_type);
            $built = $this->build($type, $data);
            $voucher = $this->vouchers->update($voucher, $built['header'], $userId);
            $voucher->items()->delete();
            $this->saveItems($voucher, $built['items']);
            $voucher->update([
                'amount' => $built['totals']['grand_total'],
                'taxable_total' => $built['totals']['taxable_total'],
                'discount_total' => $built['totals']['discount_total'],
                'discount_type' => $built['totals']['discount_type'],
                'discount_value' => $built['totals']['discount_value'],
                'tax_rate' => $built['totals']['tax_rate'],
                'tax_total' => $built['totals']['tax_total'],
                'round_off' => $built['totals']['round_off'],
                'gst_type' => $built['totals']['gst_type'],
                'due_date' => $built['due_date'],
            ]);
            return $voucher->fresh()->load($this->relations());
        });
    }

    public function delete(Voucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            $voucher->items()->delete();
            $this->vouchers->delete($voucher);
        });
    }

    public function relations(): array
    {
        return array_merge($this->vouchers->relations(), ['items.product:id,name,sku']);
    }

    public function defaultLedgers()
    {
        $defaults = config('accounting.defaults', []);
        $result = [];
        foreach ($defaults as $key => $name) {
            $result[$key] = $this->findLedger($name);
        }
        return $result;
    }

    protected function assertItemType(?string $type): string
    {
        if (!$type || !isset(config('accounting.voucher_types')[$type])) {
            throw ValidationException::withMessages(['voucher_type' => 'Unknown voucher type.']);
        }
        $cfg = config("accounting.voucher_types.{$type}");
        if (empty($cfg['items'])) {
            throw ValidationException::withMessages(['voucher_type' => 'This voucher type is not an item-based type.']);
        }
        return $type;
    }

    protected function build(string $type, array $data): array
    {
        $cfg = config("accounting.voucher_types.{$type}");
        $posting = $cfg['posting'] ?? [];
        $gstType = $data['gst_type'] ?? 'intra';
        if (!in_array($gstType, ['intra', 'inter'])) {
            $gstType = 'intra';
        }

        $items = $data['items'] ?? [];
        if (!is_array($items) || count($items) < 1) {
            throw ValidationException::withMessages(['items' => 'Add at least one line item.']);
        }
        $items = $this->normaliseItems($gstType, $items);

        $party = $this->getLedger((int)($data['party_ledger_id'] ?? 0), 'party_ledger_id');
        if ($cfg['party_type'] && $party->party_type !== $cfg['party_type']) {
            throw ValidationException::withMessages(['party_ledger_id' => 'The selected ledger is not a valid ' . $cfg['party_type'] . ' ledger.']);
        }

        $totals = $this->computeTotals($items, $data);
        $items = $this->apportionLines($items, $totals, $gstType);
        $ledgers = $this->resolveLedgers($type, $data, $posting, $totals);
        $entries = $this->buildEntries($type, $totals, $party, $ledgers, $posting);

        // What the caller typed wins; otherwise the party's credit terms set
        // the due date (no due date at all when the party has no terms).
        $dueDate = $data['due_date'] ?? $this->defaultDueDate($party, $data['voucher_date']);

        $header = [
            'voucher_type' => $type,
            'voucher_date' => $data['voucher_date'],
            'reference_no' => $data['reference_no'] ?? null,
            'narration' => $data['narration'] ?? null,
            'status' => $data['status'] ?? 'posted',
            'party_ledger_id' => $party->id,
            'entries' => $entries,
        ];
        if (!empty($data['bill_allocations'])) {
            $header['bill_allocations'] = $data['bill_allocations'];
        }

        return [
            'header' => $header,
            'due_date' => $dueDate,
            'items' => $items,
            'totals' => [
                'taxable_total' => $totals['taxable_total'],
                'discount_type' => $totals['discount_type'],
                'discount_value' => $totals['discount_value'],
                'discount_total' => $totals['discount_total'],
                'tax_rate' => $totals['tax_rate'],
                'tax_total' => $totals['tax_total'],
                'round_off' => $totals['round_off'],
                'grand_total' => $totals['grand_total'],
                'gst_type' => $gstType,
            ],
        ];
    }

    /**
     * Default due date for a bill: the party's `credit_days` after the voucher
     * date.
     *
     * A party with no terms (or a master row predating the column) returns
     * null, so nothing becomes overdue by accident — the bill then ages from
     * its document date, exactly as it did before credit days existed.
     */
    protected function defaultDueDate(LedgerAccount $party, string $date): ?string
    {
        $table = ['customer' => 'customers', 'supplier' => 'suppliers'][$party->party_type] ?? null;
        if ($table === null || $party->party_id === null || ! Schema::hasTable($table)) {
            return null;
        }

        $record = $party->party_type === 'customer'
            ? Customer::find($party->party_id)
            : Supplier::find($party->party_id);

        $days = (int) ($record?->credit_days ?? 0);

        return $days > 0
            ? Carbon::parse($date)->addDays($days)->toDateString()
            : null;
    }

    protected function normaliseItems(string $gstType, array $items): array
    {
        // Tax and discount belong to the bill, not the line. Each line is still
        // stamped with the bill's rate and a zero discount so the per-line
        // columns stay meaningful for readers that predate bill-level tax.
        $out = [];
        foreach ($items as $i) {
            $qty = round((float)($i['quantity'] ?? 0), 3);
            $rate = round((float)($i['rate'] ?? 0), 2);
            $taxable = round($qty * $rate, 2);
            $out[] = [
                'product_id' => $i['product_id'] ?? null,
                'item_description' => $i['item_description'] ?? '',
                'hsn_code' => $i['hsn_code'] ?? null,
                'unit' => $i['unit'] ?? null,
                'quantity' => $qty,
                'rate' => $rate,
                'discount_percent' => 0.0,
                'discount_amount' => 0.0,
                'taxable_amount' => $taxable,
                'tax_rate' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
                'tax_amount' => 0.0,
                'total_amount' => $taxable,
            ];
        }
        return $out;
    }

    /**
     * Document totals for a bill-level tax and discount.
     *
     * The discount (a percentage of the taxable value, or a flat amount) comes
     * off the taxable base first, then tax is charged on what remains — so a
     * discount genuinely reduces the tax, exactly as the per-line code did.
     * Each line's tax is then apportioned pro-rata on the discounted base so
     * the line columns still add up to the document totals.
     */
    protected function computeTotals(array $items, array $data): array
    {
        $gstType = ($data['gst_type'] ?? 'intra') === 'inter' ? 'inter' : 'intra';
        $taxRate = round((float)($data['tax_rate'] ?? 0), 2);
        if ($taxRate < 0 || $taxRate > 100) {
            throw ValidationException::withMessages(['tax_rate' => 'The tax rate must be between 0 and 100.']);
        }

        $taxable_total = round(array_sum(array_map(fn($x) => $x['taxable_amount'], $items)), 2);

        $discountType = ($data['discount_type'] ?? null) === 'amount' ? 'amount' : 'percent';
        $discountValue = round((float)($data['discount_value'] ?? 0), 2);
        if ($discountValue < 0) {
            throw ValidationException::withMessages(['discount_value' => 'The discount cannot be negative.']);
        }
        $discount_total = $discountType === 'amount'
            ? $discountValue
            : round($taxable_total * $discountValue / 100, 2);

        // A discount can never exceed what is being discounted.
        if ($discount_total > $taxable_total) {
            $discount_total = $taxable_total;
        }
        $discount_total = round($discount_total, 2);
        $discounted_taxable = round($taxable_total - $discount_total, 2);

        $tax_total = round($discounted_taxable * $taxRate / 100, 2);
        if ($gstType === 'inter') {
            $igst_total = $tax_total;
            $cgst_total = 0.0;
            $sgst_total = 0.0;
        } else {
            $cgst_total = round($tax_total / 2, 2);
            $sgst_total = round($tax_total - $cgst_total, 2);
            $igst_total = 0.0;
        }

        $round_off = round((float)($data['round_off'] ?? 0), 2);
        if (abs($round_off) > 1000) {
            throw ValidationException::withMessages(['round_off' => 'Round off cannot exceed 1000.']);
        }
        $grand = round($discounted_taxable + $tax_total + $round_off, 2);
        if ($grand <= 0) {
            throw ValidationException::withMessages(['items' => 'The voucher total must be greater than zero.']);
        }

        return [
            'taxable_total' => $taxable_total,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_total' => $discount_total,
            'discounted_taxable' => $discounted_taxable,
            'tax_rate' => $taxRate,
            'cgst_total' => $cgst_total,
            'sgst_total' => $sgst_total,
            'igst_total' => $igst_total,
            'tax_total' => $tax_total,
            'round_off' => $round_off,
            'grand_total' => $grand,
            'gst_type' => $gstType,
        ];
    }

    /**
     * Stamp each line with its share of the bill's discount and tax.
     *
     * The document figures are already final; this only makes the per-line
     * columns consistent with them (a line shows the same total the totals
     * panel attributes to it) without re-deriving anything.
     */
    protected function apportionLines(array $items, array $totals, string $gstType): array
    {
        $base = (float) $totals['taxable_total'];
        $out = [];

        foreach ($items as $item) {
            $gross = (float) $item['taxable_amount'];
            $discount = $base > 0
                ? round($gross / $base * $totals['discount_total'], 2)
                : 0.0;
            $taxable = round($gross - $discount, 2);
            $tax = round($taxable * $totals['tax_rate'] / 100, 2);

            if ($gstType === 'inter') {
                $igst = $tax;
                $cgst = 0.0;
                $sgst = 0.0;
            } else {
                $cgst = round($tax / 2, 2);
                $sgst = round($tax - $cgst, 2);
                $igst = 0.0;
            }

            $out[] = array_merge($item, [
                'discount_percent' => $base > 0
                    ? round($discount / $gross * 100, 2)
                    : 0.0,
                'discount_amount' => $discount,
                'taxable_amount' => $taxable,
                'tax_rate' => $totals['tax_rate'],
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'tax_amount' => round($cgst + $sgst + $igst, 2),
                'total_amount' => round($taxable + $cgst + $sgst + $igst, 2),
            ]);
        }

        return $out;
    }

    protected function resolveLedgers(string $type, array $data, array $posting, array $totals): array
    {
        $defaults = config('accounting.defaults', []);
        $out = ['base' => null, 'cgst' => null, 'sgst' => null, 'igst' => null, 'round_off' => null];

        if ($totals['taxable_total'] != 0) {
            $baseKey = $posting['base_ledger'] ?? null;
            $out['base'] = $this->findLedgerByOverrideOrName(
                $data['base_ledger_id'] ?? null,
                $defaults[$baseKey] ?? null,
                'base ledger'
            );
        }

        if (($totals['cgst_total'] ?? 0) != 0) {
            $out['cgst'] = $this->findLedgerByOverrideOrName($data['cgst_ledger_id'] ?? null, $defaults['cgst_ledger'] ?? null, 'CGST ledger');
        }
        if (($totals['sgst_total'] ?? 0) != 0) {
            $out['sgst'] = $this->findLedgerByOverrideOrName($data['sgst_ledger_id'] ?? null, $defaults['sgst_ledger'] ?? null, 'SGST ledger');
        }
        if (($totals['igst_total'] ?? 0) != 0) {
            $out['igst'] = $this->findLedgerByOverrideOrName($data['igst_ledger_id'] ?? null, $defaults['igst_ledger'] ?? null, 'IGST ledger');
        }
        if (abs($totals['round_off'] ?? 0) >= 0.005) {
            $out['round_off'] = $this->findLedgerByOverrideOrName($data['round_off_ledger_id'] ?? null, $defaults['round_off_ledger'] ?? null, 'Round Off ledger');
        }

        return $out;
    }

    protected function findLedgerByOverrideOrName(?int $overrideId, ?string $name, string $label): ?LedgerAccount
    {
        if ($overrideId) {
            return $this->getLedger($overrideId, $label);
        }
        if (!$name) {
            return null;
        }
        $ledger = $this->findLedger($name);
        if (!$ledger) {
            throw ValidationException::withMessages([$label => "Ledger '{$name}' not found. Create it under Chart of Accounts."]);
        }
        return $ledger;
    }

    protected function findLedger(string $name): ?LedgerAccount
    {
        return LedgerAccount::findByName($name);
    }

    protected function getLedger(int $id, string $field): LedgerAccount
    {
        $ledger = LedgerAccount::find($id);
        if (!$ledger) {
            throw ValidationException::withMessages([$field => 'The selected ledger does not exist.']);
        }
        return $ledger;
    }

    protected function buildEntries(string $type, array $totals, LedgerAccount $party, array $ledgers, array $posting): array
    {
        $entries = [];
        $entries[] = [
            'ledger_id' => $party->id,
            'entry_type' => $posting['party'] ?? 'Cr',
            'amount' => round($totals['grand_total'], 2),
        ];
        if ($totals['taxable_total'] != 0 && $ledgers['base']) {
            $entries[] = [
                'ledger_id' => $ledgers['base']->id,
                'entry_type' => $posting['base'] ?? 'Dr',
                // The discount is taken off the taxable value, so the base
                // ledger is charged the net figure — otherwise the entry would
                // not balance against the party total.
                'amount' => round($totals['discounted_taxable'], 2),
            ];
        }
        foreach (['cgst', 'sgst', 'igst'] as $tax) {
            $amt = $totals[$tax . '_total'] ?? 0;
            if (abs($amt) >= 0.005 && $ledgers[$tax]) {
                $entries[] = [
                    'ledger_id' => $ledgers[$tax]->id,
                    'entry_type' => $posting['base'] ?? 'Dr',
                    'amount' => round($amt, 2),
                ];
            }
        }
        if (abs($totals['round_off']) >= 0.005 && $ledgers['round_off']) {
            $roundOff = round($totals['round_off'], 2);
            $partyType = $posting['party'] ?? 'Cr';
            $baseType = $posting['base'] ?? 'Dr';
            $entries[] = [
                'ledger_id' => $ledgers['round_off']->id,
                'entry_type' => $roundOff > 0 ? $baseType : $partyType,
                'amount' => round(abs($roundOff), 2),
            ];
        }
        return $entries;
    }

    protected function saveItems(Voucher $voucher, array $items): void
    {
        $line = 1;
        foreach ($items as $item) {
            $voucher->items()->create(array_merge($item, ['line_no' => $line++]));
        }
    }
}