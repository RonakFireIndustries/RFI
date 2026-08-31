<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingFireSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'system_type', 'sub_type', 'other_details',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
