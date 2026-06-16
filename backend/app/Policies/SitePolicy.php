<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Site;
use Illuminate\Auth\Access\HandlesAuthorization;

class SitePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Site $site = null)
    {
        return $user->hasPermissionTo('view_sites');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_sites');
    }

    public function update(User $user, Site $site = null)
    {
        return $user->hasPermissionTo('update_sites');
    }

    public function delete(User $user, Site $site = null)
    {
        return $user->hasPermissionTo('delete_sites');
    }
}
