<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\User;
use App\Support\Access;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncEmployeeRoles extends Command
{
    protected $signature = 'employees:sync-roles';
    protected $description = '[Deprecated] Role-based access was removed. This now ensures every employee has the baseline core permissions as direct grants and removes any lingering role assignments (Access Control panel is the single source of truth).';

    public function handle(): int
    {
        $baseline = config('access.baseline', []);
        $baselineValid = Permission::whereIn('name', $baseline)->pluck('name')->all();

        $users = User::whereNotNull('id')->whereHas('employee')->with('roles')->get();
        $updated = 0;

        foreach ($users as $user) {
            if (Access::isSuperAdmin($user)) {
                continue;
            }

            $hadRoles = $user->roles->isNotEmpty();
            $hadBaseline = $user->hasAllPermissions($baselineValid);

            DB::transaction(function () use ($user, $baselineValid) {
                $user->givePermissionTo($baselineValid);
                $user->roles()->detach();
            });

            if ($hadRoles || !$hadBaseline) {
                $this->info("User #{$user->id} {$user->email}: baseline ensured, roles detached.");
                $updated++;
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info("Done. {$updated} user(s) updated. Roles are deprecated; use the Access Control panel instead.");
        return Command::SUCCESS;
    }
}
