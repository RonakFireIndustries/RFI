<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Category $cat = null)
    {
        return $user->hasPermissionTo('view_categories');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_categories');
    }

    public function update(User $user, Category $cat = null)
    {
        return $user->hasPermissionTo('update_categories');
    }

    public function delete(User $user, Category $cat = null)
    {
        return $user->hasPermissionTo('delete_categories');
    }
}
