<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'annual_allocation',
        'carry_forward',
        'max_consecutive_days',
        'requires_approval',
        'status',
    ];

    protected $casts = [
        'annual_allocation' => 'decimal:2',
        'carry_forward' => 'boolean',
        'requires_approval' => 'boolean',
        'max_consecutive_days' => 'integer',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
