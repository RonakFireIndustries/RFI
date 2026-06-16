<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Supplier $supplier = null)
    {
        return $user->hasPermissionTo('view_suppliers');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_suppliers');
    }

    public function update(User $user, Supplier $supplier = null)
    {
        return $user->hasPermissionTo('update_suppliers');
    }

    public function delete(User $user, Supplier $supplier = null)
    {
        return $user->hasPermissionTo('delete_suppliers');
    }
}
