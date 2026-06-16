<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Leave;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeavePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Leave $leave = null)
    {
        return $user->hasPermissionTo('view_leaves');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_leaves');
    }

    public function approve(User $user, Leave $leave = null)
    {
        return $user->hasPermissionTo('approve_leaves');
    }
}
