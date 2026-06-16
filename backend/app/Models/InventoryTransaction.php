<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class InventoryTransaction extends Model
{
    use HasFactory, BelongsToBranch;
    protected $fillable = ['inventory_id', 'user_id', 'type', 'quantity', 'reference_type', 'reference_id', 'notes'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
