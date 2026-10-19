<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Supplier extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'gst_number', 'address', 'credit_days', 'is_system'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

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

    /** The accounting ledger this master drives, created by PartyLedgerService. */
    public function ledger()
    {
        return $this->hasOne(LedgerAccount::class, 'party_id')->where('party_type', 'supplier');
    }
}
