<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('employee.view');
    }

    public function view(User $user, Employee $employee = null)
    {
        return $user->hasPermissionTo('employee.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('employee.create');
    }

    public function update(User $user, Employee $employee = null)
    {
        return $user->hasPermissionTo('employee.edit');
    }

    public function delete(User $user, Employee $employee = null)
    {
        return $user->hasPermissionTo('employee.delete');
    }
}
