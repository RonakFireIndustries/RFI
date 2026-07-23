<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptNote extends Model
{
    protected $fillable = [
        'grn_number', 'purchase_order_id', 'received_by',
        'receipt_date', 'notes',
    ];
}
