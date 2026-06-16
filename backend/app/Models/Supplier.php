<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class Supplier extends Model
{
    use BelongsToBranch;
    protected $guarded = [];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
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
}
