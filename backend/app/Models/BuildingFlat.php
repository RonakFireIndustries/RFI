<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingFlat extends Model
{
    use HasFactory;

    protected $fillable = ['building_id', 'wing_id', 'floor_id', 'name', 'flat_number', 'bhk_type', 'area'];

    protected $casts = [
        'area' => 'decimal:2',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function wing(): BelongsTo
    {
        return $this->belongsTo(BuildingWing::class, 'wing_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(BuildingFloor::class, 'floor_id');
    }
}
