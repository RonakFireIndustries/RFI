<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DailyReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyReportPolicy
{
    use HandlesAuthorization;

    public function view(User $user, DailyReport $report = null)
    {
        return $user->hasPermissionTo('view_reports');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view_reports');
    }

    public function delete(User $user, DailyReport $report = null)
    {
        return $user->hasPermissionTo('view_reports');
    }
}
