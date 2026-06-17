<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;

class LeavePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('leave.view');
    }

    public function view(User $user, Leave $leave): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'HR'])) {
            return true;
        }
        return $user->employee && $leave->employee_id === $user->employee->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('leave.create');
    }

    public function update(User $user, Leave $leave): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'HR'])) {
            return true;
        }
        // Only allow update if draft or by owner
        return $user->employee && $leave->employee_id === $user->employee->id && in_array($leave->status, ['Draft', 'Submitted']);
    }

    public function delete(User $user, Leave $leave): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'HR'])) {
            return true;
        }
        return $user->employee && $leave->employee_id === $user->employee->id && $leave->status === 'Draft';
    }

    public function approve(User $user, Leave $leave): bool
    {
        return $user->hasPermissionTo('leave.approve');
    }

    public function reject(User $user, Leave $leave): bool
    {
        return $user->hasPermissionTo('leave.reject');
    }

    public function cancel(User $user, Leave $leave): bool
    {
        return $user->hasPermissionTo('leave.cancel');
    }
}
