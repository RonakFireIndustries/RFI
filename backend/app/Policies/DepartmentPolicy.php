<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Department $dept = null)
    {
        return $user->hasPermissionTo('view_departments');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_departments');
    }

    public function update(User $user, Department $dept = null)
    {
        return $user->hasPermissionTo('update_departments');
    }

    public function delete(User $user, Department $dept = null)
    {
        return $user->hasPermissionTo('delete_departments');
    }
}
