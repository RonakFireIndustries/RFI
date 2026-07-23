<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id', 'sales_order_item_id', 'product_id',
        'delivered_quantity',
    ];
}
