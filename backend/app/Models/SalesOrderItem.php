<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class SalesOrderItem extends Model
{
    use BelongsToBranch;
    protected $fillable = ['sales_order_id', 'product_id', 'quantity', 'delivered_quantity', 'unit_price', 'gst_amount', 'discount', 'total'];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
