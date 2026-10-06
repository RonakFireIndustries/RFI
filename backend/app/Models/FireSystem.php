<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FireSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'contact_name', 'contact_number',
        'system_type', 'sub_type', 'status', 'quantity',
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

    public const STATUS_AVAILABLE = 'Available';
    public const STATUS_REFILLING = 'Taken for Refilling';
    public const STATUS_DAMAGED = 'Damaged';
    public const STATUS_DECOMMISSIONED = 'Decommissioned';

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_REFILLING,
        self::STATUS_DAMAGED,
        self::STATUS_DECOMMISSIONED,
    ];

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

    public function movements(): HasMany
    {
        return $this->hasMany(ExtinguisherMovement::class);
    }
}
