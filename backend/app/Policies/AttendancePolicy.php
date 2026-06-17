<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('attendance.view') || $user->hasPermissionTo('view_attendance');
    }

    public function view(User $user, Attendance $attendance = null)
    {
        return $user->hasPermissionTo('attendance.view') || $user->hasPermissionTo('view_attendance');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('attendance.create') || $user->hasPermissionTo('create_attendance');
    }

    public function update(User $user, Attendance $attendance = null)
    {
        return $user->hasPermissionTo('attendance.edit') || $user->hasPermissionTo('update_attendance');
    }

    public function delete(User $user, Attendance $attendance = null)
    {
        return $user->hasPermissionTo('attendance.delete') || $user->hasPermissionTo('delete_attendance');
    }
}
