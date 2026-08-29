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
        'label', 'installation_date', 'next_refill_date',
        'location', 'type', 'year_of_manufacturing', 'remark',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'installation_year' => 'integer',
        'year_of_manufacturing' => 'integer',
        'last_testing_date' => 'date',
        'installation_date' => 'date',
        'next_refill_date' => 'date',
    ];

    public const TYPE_EXTINGUISHER = 'Fire Extinguisher';

    /**
     * Scope to fire-extinguisher records only.
     */
    public function scopeExtinguishers($query)
    {
        return $query->where('system_type', self::TYPE_EXTINGUISHER);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
