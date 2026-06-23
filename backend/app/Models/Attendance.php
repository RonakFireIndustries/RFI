<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'site_id',
        'shift_id',
        'date',
        'check_in',
        'check_out',
        'working_hours',
        'overtime_hours',
        'status',
        'remarks',
        'checkin_latitude',
        'checkin_longitude',
        'checkout_latitude',
        'checkout_longitude',
        'checkin_distance',
        'checkout_distance',
        'location_verified',
        'accuracy',
        'device_info',
        'browser_info',
        'ip_address',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'working_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'checkin_latitude' => 'decimal:8',
        'checkin_longitude' => 'decimal:8',
        'checkout_latitude' => 'decimal:8',
        'checkout_longitude' => 'decimal:8',
        'checkin_distance' => 'decimal:2',
        'checkout_distance' => 'decimal:2',
        'location_verified' => 'boolean',
        'accuracy' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $attendance) {
            if ($attendance->check_in && $attendance->check_out) {
                $diffMins = $attendance->check_in->diffInMinutes($attendance->check_out);
                $diffHours = round($diffMins / 60, 2);
                $attendance->working_hours = $diffHours;
                if ($diffHours > 8) {
                    $attendance->overtime_hours = round($diffHours - 8, 2);
                    $attendance->overtime = $attendance->overtime_hours;
                } else {
                    $attendance->overtime_hours = 0;
                    $attendance->overtime = 0;
                }
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
