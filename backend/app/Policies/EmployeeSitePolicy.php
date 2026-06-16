<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EmployeeSite;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeSitePolicy
{
    use HandlesAuthorization;

    public function view(User $user, EmployeeSite $es = null)
    {
        return $user->hasPermissionTo('view_employees');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_employees');
    }

    public function delete(User $user, EmployeeSite $es = null)
    {
        return $user->hasPermissionTo('update_employees');
    }
}
