<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Access;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccessSetupCommand extends Command
{
    protected $signature = 'access:setup
                            {--email= : Email of the user to mark as Super Admin (optional)}
                            {--copy : Also copy legacy grants (default true)}';

    protected $description = 'Seed canonical permissions, copy legacy grants, and mark a Super Admin.';

    public function handle(): int
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create canonical permissions
        $canonicalNames = Access::allPermissionNames();
        $maxId = \Spatie\Permission\Models\Permission::max('id') ?? 0;
        $created = 0;
        foreach ($canonicalNames as $name) {
            $exists = \Spatie\Permission\Models\Permission::where('name', $name)->first();
            if (!$exists) {
                $maxId++;
                DB::table('permissions')->insert([
                    'id' => $maxId,
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }
        }
        $this->info("Canonical permissions ensured ({$created} created).");

        // 2. Copy legacy grants
        if ($this->option('copy')) {
            $this->copyLegacyGrants();
        }

        // 3. Mark a super admin
        $email = $this->option('email');
        if ($email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("No user found with email {$email}");
                return self::FAILURE;
            }
            $user->update(['is_super_admin' => true]);
            $this->info("Marked {$email} as Super Admin.");
        } else {
            // Default: mark the first Admin-role user as super admin if none flagged yet
            $anySuper = User::where('is_super_admin', true)->exists();
            if (!$anySuper) {
                $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'Admin'))->first();
                if ($admin) {
                    $admin->update(['is_super_admin' => true]);
                    $this->info("Marked {$admin->email} as Super Admin (first Admin-role user).");
                } else {
                    $this->warn('No Admin-role user found; set one with --email.');
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('Permission cache cleared.');
        return self::SUCCESS;
    }

    protected function copyLegacyGrants(): void
    {
        $aliases = Access::legacyAliases();
        $morphKey = config('permission.column_names.model_morph_key', 'model_morph_key');
        $copiedUser = 0;
        $copiedRole = 0;

        foreach ($aliases as $legacyName => $canonicalName) {
            $legacyPerm = \Spatie\Permission\Models\Permission::where('name', $legacyName)->first();
            $canonicalPerm = \Spatie\Permission\Models\Permission::where('name', $canonicalName)->first();
            if (!$legacyPerm || !$canonicalPerm) {
                continue;
            }

            foreach (DB::table('model_has_permissions')
                ->where('permission_id', $legacyPerm->id)
                ->get(['model_type', $morphKey]) as $g) {
                $exists = DB::table('model_has_permissions')
                    ->where('permission_id', $canonicalPerm->id)
                    ->where('model_type', $g->model_type)
                    ->where($morphKey, $g->{$morphKey})
                    ->exists();
                if (!$exists) {
                    DB::table('model_has_permissions')->insert([
                        'permission_id' => $canonicalPerm->id,
                        'model_type' => $g->model_type,
                        $morphKey => $g->{$morphKey},
                    ]);
                    $copiedUser++;
                }
            }

            foreach (DB::table('role_has_permissions')
                ->where('permission_id', $legacyPerm->id)
                ->get(['role_id']) as $g) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $canonicalPerm->id)
                    ->where('role_id', $g->role_id)
                    ->exists();
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $canonicalPerm->id,
                        'role_id' => $g->role_id,
                    ]);
                    $copiedRole++;
                }
            }
        }

        $this->info("Copied legacy grants: {$copiedUser} per-user, {$copiedRole} per-role.");
    }
}
