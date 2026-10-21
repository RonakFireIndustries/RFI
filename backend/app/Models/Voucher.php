<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A double-entry voucher — the Tally-style unit of posting.
 *
 * The header stores the type key (config/accounting.php), its number and date,
 * and the totals + party pointer. The Dr/Cr lines live in `voucher_entries`;
 * any bill-wise details in `voucher_bill_allocations`.
 */
class Voucher extends Model
{
    protected $fillable = [
        'voucher_type',
        'voucher_no',
        'voucher_date',
        'due_date',
        'financial_year_id',
        'reference_no',
        'narration',
        'status',
        'party_ledger_id',
        'amount',
        'taxable_total',
        'discount_total',
        'tax_total',
        'round_off',
        'gst_type',
        'tax_rate',
        'discount_type',
        'discount_value',
        'created_by',
        'updated_by',
        'posted_at',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'taxable_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'round_off' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function entries()
    {
        return $this->hasMany(VoucherEntry::class)->orderBy('line_no')->orderBy('id');
    }

    public function items()
    {
        return $this->hasMany(VoucherItem::class)->orderBy('line_no')->orderBy('id');
    }

    public function billAllocations()
    {
        return $this->hasMany(VoucherBillAllocation::class);
    }

    /**
     * Goods movements against this document: receipts for a purchase, deliveries
     * for a sale. Reversed rows stay in the history.
     */
    public function stockReceipts()
    {
        return $this->hasMany(StockReceipt::class)->orderBy('receipt_date')->orderBy('id');
    }

    public function activeStockReceipts()
    {
        return $this->hasMany(StockReceipt::class)->where('status', 'active');
    }

    /**
     * Whether this type moves warehouse stock at all, and in which direction.
     * Null for purely financial documents (credit notes, journals, …).
     */
    public function stockDirection(): ?string
    {
        return config("accounting.voucher_types.{$this->voucher_type}.stock");
    }

    public function tracksStock(): bool
    {
        return $this->stockDirection() !== null;
    }

    public function party()
    {
        return $this->belongsTo(LedgerAccount::class, 'party_ledger_id');
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Debit total of the voucher (equals the credit total when balanced). */
    public function getDebitTotalAttribute(): float
    {
        return round((float) $this->entries->where('entry_type', 'Dr')->sum('amount'), 2);
    }

    public function getCreditTotalAttribute(): float
    {
        return round((float) $this->entries->where('entry_type', 'Cr')->sum('amount'), 2);
    }

    public function isBalanced(): bool
    {
        return abs($this->debit_total - $this->credit_total) < 0.01;
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('voucher_type', $type);
    }
}
