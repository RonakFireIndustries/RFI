<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A financial year, e.g. "2025-2026". One year is active at a time.
 */
class FinancialYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
    ];

    /** The currently active financial year, if any. */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
