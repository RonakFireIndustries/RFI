<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payroll $payroll = null)
    {
        return $user->hasPermissionTo('view_payroll');
    }

    public function manage(User $user, Payroll $payroll = null)
    {
        return $user->hasPermissionTo('manage_payroll');
    }

    public function process(User $user)
    {
        return $user->hasPermissionTo('manage_payroll');
    }
}
