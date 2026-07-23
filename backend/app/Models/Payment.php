<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    protected $fillable = [
        'type',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'supplier_id',
        'customer_id',
        'purchase_order_id',
        'sales_order_id',
        'created_by',
    ];
}
