<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payment $payment = null)
    {
        return $user->hasPermissionTo('view_payments');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_payments');
    }
}
