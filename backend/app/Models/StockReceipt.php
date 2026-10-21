<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One physical movement of goods against an item voucher.
 *
 * For a purchase voucher this is a goods receipt note: the supplier delivered
 * 6 of the 10 pumps on the bill. For a sales voucher it is the delivery note
 * for the same table, distinguished by `voucher_type`. Receipts are additive —
 * three part-deliveries are three rows — and reversible, so the warehouse story
 * is preserved rather than overwritten.
 */
class StockReceipt extends Model
{
    protected $fillable = [
        'receipt_no',
        'voucher_type',
        'voucher_id',
        'receipt_date',
        'party_ledger_id',
        'location_type',
        'location_id',
        'status',
        'reversal_reason',
        'notes',
        'supplier_bill_no',
        'supplier_bill_date',
        'attachment_path',
        'received_by',
        'reversed_by',
        'reversed_at',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'supplier_bill_date' => 'date',
        'reversed_at' => 'datetime',
        'location_id' => 'integer',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'party_ledger_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * The site (or future warehouse) the goods moved to / from. Named for the
     * polymorphic pair the way product_stock and transaction_ledger name it.
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo('locationable', 'location_type', 'location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }
}
