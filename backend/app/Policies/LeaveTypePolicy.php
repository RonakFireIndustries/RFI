<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('leave-type.view');
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        return $user->hasPermissionTo('leave-type.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('leave-type.create');
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $user->hasPermissionTo('leave-type.edit');
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $user->hasPermissionTo('leave-type.delete');
    }
}
