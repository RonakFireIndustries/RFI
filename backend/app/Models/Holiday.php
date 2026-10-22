<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A holiday declared by the company on its own calendar.
 *
 * The row is the single source of truth for "the company is closed on this
 * date": payroll, attendance and leave all read this table to decide which
 * days are working days.
 */
class Holiday extends Model
{
    use HasFactory;

    /**
     * The type vocabulary the company calendar supports.
     *
     * The stored value IS the label - the report pack groups on the raw
     * `type` string, so there is no separate lookup table to keep in sync.
     */
    public const TYPE_PUBLIC = 'Public Holiday';
    public const TYPE_RESTRICTED = 'Restricted Holiday';
    public const TYPE_OPTIONAL = 'Optional Holiday';

    public const TYPES = [
        self::TYPE_PUBLIC,
        self::TYPE_RESTRICTED,
        self::TYPE_OPTIONAL,
    ];

    /**
     * Aliases accepted on input so a caller can post the short form
     * ("Public") and still land on the canonical label.
     */
    public const TYPE_ALIASES = [
        'public' => self::TYPE_PUBLIC,
        'public holiday' => self::TYPE_PUBLIC,
        'national' => self::TYPE_PUBLIC,
        'national holiday' => self::TYPE_PUBLIC,
        'gazetted' => self::TYPE_PUBLIC,
        'gazetted holiday' => self::TYPE_PUBLIC,
        'restricted' => self::TYPE_RESTRICTED,
        'restricted holiday' => self::TYPE_RESTRICTED,
        'optional' => self::TYPE_OPTIONAL,
        'optional holiday' => self::TYPE_OPTIONAL,
    ];

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    protected $fillable = [
        'name',
        'date',
        'type',
        'status',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Only Active holidays are working-day exceptions.
     *
     * An Inactive row stays on the calendar as history (e.g. a festival that
     * was called off) but no longer shortens the working month in payroll or
     * attendance, and no longer blocks a leave request.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Restrict to the calendar of a single year.
     */
    public function scopeForYear(Builder $query, int|string $year): Builder
    {
        return $query->whereBetween('date', [
            sprintf('%04d-01-01', $year),
            sprintf('%04d-12-31', $year),
        ]);
    }

    /**
     * Restrict to a closed date range (inclusive).
     */
    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }
}
