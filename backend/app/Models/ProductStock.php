<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $table = 'product_stock';

    protected $fillable = [
        'product_id', 'location_type', 'location_id', 'quantity',
        'reserved_quantity', 'available_quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function locationable()
    {
        return $this->morphTo('locationable', 'location_type', 'location_id');
    }
}
