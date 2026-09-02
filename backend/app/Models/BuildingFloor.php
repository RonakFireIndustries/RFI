<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuildingFloor extends Model
{
    use HasFactory;

    protected $fillable = ['building_id', 'wing_id', 'name', 'floor_number', 'type', 'area', 'count'];

    protected $casts = [
        'floor_number' => 'integer',
        'area' => 'decimal:2',
        'count' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function wing(): BelongsTo
    {
        return $this->belongsTo(BuildingWing::class, 'wing_id');
    }

    public function flats(): HasMany
    {
        return $this->hasMany(BuildingFlat::class, 'floor_id');
    }
}
