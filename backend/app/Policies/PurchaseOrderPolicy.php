<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PurchaseOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, PurchaseOrder $order = null)
    {
        return $user->hasPermissionTo('view_purchase_orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_purchase_orders');
    }

    public function approve(User $user, PurchaseOrder $order = null)
    {
        return $user->hasPermissionTo('approve_purchase_orders');
    }
}
