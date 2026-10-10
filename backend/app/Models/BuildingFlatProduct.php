<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingFlatProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'flat_id', 'site_id',
        'product_name', 'brand', 'model', 'quantity', 'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function flat(): BelongsTo
    {
        return $this->belongsTo(BuildingFlat::class, 'flat_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
