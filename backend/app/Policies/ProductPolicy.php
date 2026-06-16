<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Product $product = null)
    {
        return $user->hasPermissionTo('view_products');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_products');
    }

    public function update(User $user, Product $product = null)
    {
        return $user->hasPermissionTo('update_products');
    }

    public function delete(User $user, Product $product = null)
    {
        return $user->hasPermissionTo('delete_products');
    }
}
