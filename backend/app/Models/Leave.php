<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Access;

class Leave extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'is_half_day',
        'status',
        'approved_by',
        'reason',
        'attachment_path',
        'comments',
        'applied_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_half_day' => 'boolean',
        'total_days' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories()
    {
        return $this->hasMany(LeaveHistory::class);
    }

    public function canBeManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (Access::isSuperAdmin($user)) {
            return true;
        }

        if ($user->hasAnyPermission(['leaves.approve', 'leaves.reject'])) {
            return true;
        }

        return $this->employee_id !== null
            && $this->employee_id === $user->employee?->id;
    }
}
