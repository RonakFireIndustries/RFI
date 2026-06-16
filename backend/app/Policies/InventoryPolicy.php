<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Inventory;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Inventory $inventory = null)
    {
        return $user->hasPermissionTo('view_inventory');
    }

    public function manage(User $user, Inventory $inventory = null)
    {
        return $user->hasPermissionTo('manage_inventory');
    }
}
