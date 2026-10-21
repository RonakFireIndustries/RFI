<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherItem extends Model
{
    protected $fillable = [
        'voucher_id',
        'line_no',
        'product_id',
        'item_description',
        'hsn_code',
        'unit',
        'quantity',
        'received_quantity',
        'delivered_quantity',
        'rate',
        'discount_percent',
        'discount_amount',
        'taxable_amount',
        'tax_rate',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'tax_amount',
        'total_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'delivered_quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'product_id' => 'integer',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The receipt lines that fulfilled this item, oldest first.
     */
    public function receivedLines(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class)->orderBy('id');
    }

    /**
     * How much of this line is still to be received (purchase) or delivered
     * (sales). Never negative, even when a receipt was keyed against a quantity
     * that was later edited down.
     */
    public function getPendingQuantityAttribute(): float
    {
        return max(0, round((float) $this->quantity - $this->moved_quantity, 3));
    }

    /** The received / delivered total, whichever side this line is on. */
    public function getMovedQuantityAttribute(): float
    {
        return round((float) ($this->isOutbound() ? $this->delivered_quantity : $this->received_quantity), 3);
    }

    /**
     * Whether receipts write the delivered counter (`sales`) or the received
     * one (`purchase`, `debit-note`). Driven by the parent voucher's `stock`
     * direction in config/accounting.php so the two never disagree.
     */
    public function isOutbound(): bool
    {
        $type = $this->relationLoaded('voucher') ? $this->voucher?->voucher_type : null;
        $type ??= Voucher::whereKey($this->voucher_id)->value('voucher_type');

        return config("accounting.voucher_types.{$type}.stock") === 'out';
    }

    /** The counter a receipt should add to for this line. */
    public function movementColumn(): string
    {
        return $this->isOutbound() ? 'delivered_quantity' : 'received_quantity';
    }
}
