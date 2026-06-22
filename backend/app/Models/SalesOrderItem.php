<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SalesOrderItem extends Model
{
    protected $fillable = ['sales_order_id', 'product_id', 'quantity', 'delivered_quantity', 'unit_price', 'gst_rate', 'hsn_code', 'discount', 'total'];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
