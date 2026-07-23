<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SalesOrder extends Model
{
    protected $fillable = [
        'so_number', 'customer_id', 'total_amount', 'status', 'created_by',
        'approved_by', 'notes', 'tax_amount', 'discount_amount',
        'shipping_cost', 'gst_type', 'gst_rate', 'delivered_by', 'delivered_at',
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'sales_order_items')
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps();
    }
}
