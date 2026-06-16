<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Customer $customer = null)
    {
        return $user->hasPermissionTo('view_customers');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_customers');
    }

    public function update(User $user, Customer $customer = null)
    {
        return $user->hasPermissionTo('update_customers');
    }

    public function delete(User $user, Customer $customer = null)
    {
        return $user->hasPermissionTo('delete_customers');
    }
}
