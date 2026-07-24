<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingWing extends Model
{
    use HasFactory;

    protected $fillable = ['building_id', 'name', 'floors', 'flats_per_floor', 'flat_configuration', 'total_flats'];

    protected $casts = [
        'floors' => 'integer',
        'flats_per_floor' => 'integer',
        'total_flats' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
