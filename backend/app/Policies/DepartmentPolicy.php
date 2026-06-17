<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('department.view');
    }

    public function view(User $user, Department $dept = null)
    {
        return $user->hasPermissionTo('department.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('department.create');
    }

    public function update(User $user, Department $dept = null)
    {
        return $user->hasPermissionTo('department.edit');
    }

    public function delete(User $user, Department $dept = null)
    {
        return $user->hasPermissionTo('department.delete');
    }
}
