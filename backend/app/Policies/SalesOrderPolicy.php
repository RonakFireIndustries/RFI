<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalesOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesOrderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, SalesOrder $order = null)
    {
        return $user->hasPermissionTo('view_sales_orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_sales_orders');
    }

    public function approve(User $user, SalesOrder $order = null)
    {
        return $user->hasPermissionTo('approve_sales_orders');
    }
}
