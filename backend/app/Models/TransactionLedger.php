<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLedger extends Model
{
    protected $table = 'transaction_ledger';

    protected $fillable = [
        'transaction_number', 'product_id', 'location_type', 'location_id',
        'to_location_type', 'to_location_id',
        'transaction_type', 'quantity', 'unit_price', 'total_price',
        'quantity_before', 'quantity_after', 'reference_type', 'reference_id',
        'notes', 'created_by'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function locationable()
    {
        return $this->morphTo('locationable', 'location_type', 'location_id');
    }

    public function toLocationable()
    {
        return $this->morphTo('to_locationable', 'to_location_type', 'to_location_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
