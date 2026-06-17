<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_period',
        'late_threshold',
        'half_day_threshold',
        'status',
        'branch_id',
    ];

    protected $casts = [
        'grace_period' => 'integer',
        'late_threshold' => 'integer',
        'half_day_threshold' => 'integer',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
