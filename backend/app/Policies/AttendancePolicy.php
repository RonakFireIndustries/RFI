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

    public function geoCheckin(User $user)
    {
        return $user->hasPermissionTo('attendance.geo.checkin') || $user->hasPermissionTo('checkin_attendance');
    }

    public function geoCheckout(User $user)
    {
        return $user->hasPermissionTo('attendance.geo.checkout') || $user->hasPermissionTo('checkout_attendance');
    }

    public function locationView(User $user)
    {
        return $user->hasPermissionTo('attendance.location.view') || $user->hasPermissionTo('view_attendance_location');
    }

    public function locationAudit(User $user)
    {
        return $user->hasPermissionTo('attendance.location.audit') || $user->hasPermissionTo('audit_attendance_location');
    }
}
