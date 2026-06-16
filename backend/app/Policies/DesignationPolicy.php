<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Designation;
use Illuminate\Auth\Access\HandlesAuthorization;

class DesignationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Designation $desg = null)
    {
        return $user->hasPermissionTo('view_designations');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_designations');
    }

    public function update(User $user, Designation $desg = null)
    {
        return $user->hasPermissionTo('update_designations');
    }

    public function delete(User $user, Designation $desg = null)
    {
        return $user->hasPermissionTo('delete_designations');
    }
}
