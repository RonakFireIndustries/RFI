<?php

namespace App\Policies;

use App\Models\LeaveBalance;
use App\Models\User;

class LeaveBalancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('leave.balance.view');
    }

    public function view(User $user, LeaveBalance $balance): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'HR'])) {
            return true;
        }
        return $user->employee && $balance->employee_id === $user->employee->id;
    }

    public function create(User $user): bool
    {
        // Typically HR / Admin can initialize balances
        return $user->hasRole(['Super Admin', 'Admin', 'HR']);
    }
}
