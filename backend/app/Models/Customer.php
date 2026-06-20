<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Customer extends Model
{
    protected $guarded = [];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
