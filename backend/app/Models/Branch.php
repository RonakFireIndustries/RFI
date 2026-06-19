<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stock(): MorphMany
    {
        return $this->morphMany(ProductStock::class, 'locationable', 'location_type', 'location_id');
    }

    public function outgoingTransactions(): MorphMany
    {
        return $this->morphMany(TransactionLedger::class, 'locationable', 'location_type', 'location_id');
    }

    public function incomingTransactions(): MorphMany
    {
        return $this->morphMany(TransactionLedger::class, 'to_locationable', 'to_location_type', 'to_location_id');
    }

    public function outgoingStockRequests(): MorphMany
    {
        return $this->morphMany(StockRequest::class, 'from_locationable', 'from_location_type', 'from_location_id');
    }

    public function incomingStockRequests(): MorphMany
    {
        return $this->morphMany(StockRequest::class, 'to_locationable', 'to_location_type', 'to_location_id');
    }
}
