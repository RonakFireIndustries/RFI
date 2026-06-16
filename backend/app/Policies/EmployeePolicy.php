<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Employee $employee = null)
    {
        return $user->hasPermissionTo('view_employees');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_employees');
    }

    public function update(User $user, Employee $employee = null)
    {
        return $user->hasPermissionTo('update_employees');
    }

    public function delete(User $user, Employee $employee = null)
    {
        return $user->hasPermissionTo('delete_employees');
    }
}
