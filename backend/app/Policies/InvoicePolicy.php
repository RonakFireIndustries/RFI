<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Invoice $invoice = null)
    {
        return $user->hasPermissionTo('view_invoices');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_invoices');
    }

    public function update(User $user, Invoice $invoice = null)
    {
        return $user->hasPermissionTo('update_invoices');
    }

    public function delete(User $user, Invoice $invoice = null)
    {
        return $user->hasPermissionTo('delete_invoices');
    }
}
