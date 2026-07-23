<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $fillable = [
        'delivery_number', 'sales_order_id', 'delivered_by',
        'delivery_date', 'notes',
    ];
}
