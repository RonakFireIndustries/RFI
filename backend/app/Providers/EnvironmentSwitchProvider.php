<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EnvironmentSwitchProvider extends ServiceProvider
{
    public function register(): void
    {
        $env = config('environment.environments.'.config('environment.active'), []);

        if (! $env) {
            return;
        }

        $this->app->config->set([
            'app.env' => $env['APP_ENV'] ?? config('app.env'),
            'app.debug' => (bool) ($env['APP_DEBUG'] ?? config('app.debug')),
            'app.url' => $env['APP_URL'] ?? config('app.url'),
            'app.frontend_url' => $env['FRONTEND_URL'] ?? config('app.frontend_url'),

            'database.connections.mysql.host' => $env['DB_HOST'] ?? config('database.connections.mysql.host'),
            'database.connections.mysql.port' => $env['DB_PORT'] ?? config('database.connections.mysql.port'),
            'database.connections.mysql.database' => $env['DB_DATABASE'] ?? config('database.connections.mysql.database'),
            'database.connections.mysql.username' => $env['DB_USERNAME'] ?? config('database.connections.mysql.username'),
            'database.connections.mysql.password' => $env['DB_PASSWORD'] ?? config('database.connections.mysql.password'),

            'filesystems.disks.local.url' => rtrim($env['APP_URL'] ?? config('app.url'), '/').'/storage',

            'broadcasting.connections.pusher.key' => $env['PUSHER_APP_KEY'] ?? config('broadcasting.connections.pusher.key'),
            'broadcasting.connections.pusher.secret' => $env['PUSHER_APP_SECRET'] ?? config('broadcasting.connections.pusher.secret'),
            'broadcasting.connections.pusher.app_id' => $env['PUSHER_APP_ID'] ?? config('broadcasting.connections.pusher.app_id'),
            'broadcasting.connections.pusher.options.cluster' => $env['PUSHER_APP_CLUSTER'] ?? config('broadcasting.connections.pusher.options.cluster'),

            'sanctum.stateful' => isset($env['SANCTUM_STATEFUL_DOMAINS'])
                ? explode(',', $env['SANCTUM_STATEFUL_DOMAINS'])
                : config('sanctum.stateful'),
        ]);
    }
}
