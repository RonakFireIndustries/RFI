<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force-clear Spatie's in-memory permission cache on first request after deploy.
        // PHP-FPM workers hold stale permission data in memory; permission:cache-reset
        // only clears the Laravel cache store, NOT the in-memory array on each worker.
        $flag = storage_path('framework/cache/.permissions-cache-cleared');
        if (!file_exists($flag)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            @touch($flag);
            @chmod($flag, 0664);
        }

        Gate::before(function ($user, $ability) {
            if (\App\Support\Access::isSuperAdmin($user)) {
                return true;
            }

            try {
                if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($ability)) {
                    return true;
                }
            } catch (PermissionDoesNotExist $e) {
                // Permission row is missing in the database (seed not run on this
                // environment). Treat it as "not granted" instead of throwing a 500.
                return null;
            }

            return null;
        });
    }
}
