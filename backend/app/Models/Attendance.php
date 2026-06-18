<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory, BelongsToBranch;

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
        'branch_id',
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
        'date' => 'date',
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
