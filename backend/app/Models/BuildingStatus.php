<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuildingStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'marker_color'];

    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class, 'building_status_mapping');
    }
}
