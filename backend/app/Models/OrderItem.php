<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One line of an order: what is being bought or sold, at what rate and tax.
 *
 * Totals are computed server-side when the order is saved (see
 * OrderService::normaliseItems) and stored for display — an order never
 * reaches a ledger, so these figures are advisory rather than book values.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'item_description',
        'hsn_code',
        'unit',
        'quantity',
        'rate',
        'discount_percent',
        'tax_rate',
        'taxable_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'position',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, OrderItem> */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, OrderItem> */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
