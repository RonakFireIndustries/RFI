<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    protected $fillable = [
        'request_number', 'product_id',
        'from_location_type', 'from_location_id',
        'to_location_type', 'to_location_id',
        'quantity', 'approved_quantity', 'issued_quantity', 'received_quantity',
        'status', 'requested_by', 'approved_by', 'issued_by', 'received_by', 'notes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromLocationable()
    {
        return $this->morphTo('from_locationable', 'from_location_type', 'from_location_id');
    }

    public function toLocationable()
    {
        return $this->morphTo('to_locationable', 'to_location_type', 'to_location_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
