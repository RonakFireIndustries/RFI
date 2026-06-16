<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Attendance $attendance = null)
    {
        return $user->hasPermissionTo('view_attendance');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_attendance');
    }

    public function update(User $user, Attendance $attendance = null)
    {
        return $user->hasPermissionTo('update_attendance');
    }
}
