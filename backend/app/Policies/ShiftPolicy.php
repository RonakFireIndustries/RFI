<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShiftPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('shift.view');
    }

    public function view(User $user, Shift $shift = null)
    {
        return $user->hasPermissionTo('shift.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('shift.create');
    }

    public function update(User $user, Shift $shift = null)
    {
        return $user->hasPermissionTo('shift.edit');
    }

    public function delete(User $user, Shift $shift = null)
    {
        return $user->hasPermissionTo('shift.delete');
    }
}
