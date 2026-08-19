<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingAmc extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'vendor_name', 'contract_number', 'contract_type',
        'start_date', 'end_date', 'amount', 'frequency', 'status',
        'scope', 'last_service_date', 'next_service_date', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
