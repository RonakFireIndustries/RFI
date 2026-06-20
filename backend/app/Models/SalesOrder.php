<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SalesOrder extends Model
{
    protected $guarded = [];

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
