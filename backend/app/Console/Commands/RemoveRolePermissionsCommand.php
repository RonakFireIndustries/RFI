<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Access;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RemoveRolePermissionsCommand extends Command
{
    protected $signature = 'access:remove-roles
                            {--dry-run : Report what would change without applying}
                            {--user= : Only process this user id (optional)}';

    protected $description = 'Move employees to direct (Access Control) permissions by converting role grants to direct grants and removing their roles. Super admins are left untouched.';

    public function handle(): int
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $baseline = config('access.baseline', []);
        $dryRun = (bool) $this->option('dry-run');
        $onlyUser = (int) $this->option('user');

        $query = User::query();
        if ($onlyUser) {
            $query->whereKey($onlyUser);
        }

        $users = $query->get();
        $this->info(sprintf('Processing %d user(s)%s.', $users->count(), $dryRun ? ' (DRY RUN)' : ''));

        $converted = 0;
        $skipped = 0;
        $noRoles = 0;

        foreach ($users as $user) {
            if (Access::isSuperAdmin($user)) {
                $this->warn("User #{$user->id} {$user->email}: super admin, skipping.");
                $skipped++;
                continue;
            }

            $currentDirect = $user->getDirectPermissions()->pluck('name')->all();
            $roleDerived = $user->getAllPermissions()->pluck('name')->all();
            $fromRoles = array_values(array_diff($roleDerived, $currentDirect));

            $roleNames = $user->roles->pluck('name')->all();
            if (empty($roleNames) && empty($fromRoles)) {
                $noRoles++;
                continue;
            }

            // Effective set to keep = current direct + role-derived + baseline.
            // Only persist permissions that actually exist in the DB.
            $desired = array_values(array_unique(array_merge($currentDirect, $roleDerived, $baseline)));
            $validNames = Permission::whereIn('name', $desired)->pluck('name')->all();

            $this->line("User #{$user->id} {$user->email}: roles [" . implode(', ', $roleNames ?: ['none']) . "]");

            if ($dryRun) {
                $this->line('   -> would keep ' . count($validNames) . ' direct perms, detach ' . count($roleNames) . ' role(s).');
                $converted++;
                continue;
            }

            DB::transaction(function () use ($user, $validNames) {
                $user->syncPermissions($validNames);
                $user->roles()->detach();
            });

            $this->line('   -> kept ' . count($validNames) . ' direct perms, detached ' . count($roleNames) . ' role(s).');
            $converted++;
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("Done. Converted: {$converted}, never had roles: {$noRoles}, skipped (super admin): {$skipped}.");
        return Command::SUCCESS;
    }
}
