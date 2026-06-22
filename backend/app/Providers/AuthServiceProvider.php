<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if (!$user) {
                return null;
            }

            $permissions = $user->roles
                ->loadMissing('permissions')
                ->flatMap(fn($role) => $role->permissions->pluck('name'))
                ->unique()
                ->values();

            return $permissions->contains($ability) ? true : null;
        });
    }
}
