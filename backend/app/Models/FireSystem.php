<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FireSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'system_type', 'sub_type', 'quantity',
        'capacity', 'brand', 'installation_year', 'last_testing_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'installation_year' => 'integer',
        'last_testing_date' => 'date',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
