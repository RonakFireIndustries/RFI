<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Designation;
use Illuminate\Auth\Access\HandlesAuthorization;

class DesignationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('designation.view');
    }

    public function view(User $user, Designation $desg = null)
    {
        return $user->hasPermissionTo('designation.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('designation.create');
    }

    public function update(User $user, Designation $desg = null)
    {
        return $user->hasPermissionTo('designation.edit');
    }

    public function delete(User $user, Designation $desg = null)
    {
        return $user->hasPermissionTo('designation.delete');
    }
}
