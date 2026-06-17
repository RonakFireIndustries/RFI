<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DailyReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('daily-report.view');
    }

    public function view(User $user, DailyReport $dailyReport)
    {
        return $user->hasPermissionTo('daily-report.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('daily-report.create');
    }

    public function update(User $user, DailyReport $dailyReport)
    {
        return $user->hasPermissionTo('daily-report.edit');
    }

    public function delete(User $user, DailyReport $dailyReport)
    {
        return $user->hasPermissionTo('daily-report.delete');
    }

    public function approve(User $user, DailyReport $dailyReport)
    {
        return $user->hasPermissionTo('daily-report.approve');
    }

    public function reject(User $user, DailyReport $dailyReport)
    {
        return $user->hasPermissionTo('daily-report.reject');
    }

    public function rework(User $user, DailyReport $dailyReport)
    {
        return $user->hasPermissionTo('daily-report.rework');
    }
}
