<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $fillable = [
        'goods_receipt_note_id', 'purchase_order_item_id', 'product_id',
        'received_quantity',
    ];
}
