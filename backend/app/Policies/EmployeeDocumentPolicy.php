<?php

namespace App\Policies;

use App\Models\EmployeeDocument;
use App\Models\User;

class EmployeeDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('document.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmployeeDocument $document): bool
    {
        return $user->can('document.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('document.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmployeeDocument $document): bool
    {
        return $user->can('document.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->can('document.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmployeeDocument $document): bool
    {
        return $user->can('document.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmployeeDocument $document): bool
    {
        return $user->can('document.delete');
    }
}
