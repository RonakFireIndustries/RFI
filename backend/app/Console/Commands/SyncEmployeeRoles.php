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
    protected $description = '[Deprecated] Role-based access was removed. Ensures baseline + Designer quotation grants as direct permissions and detaches any lingering roles (Access Control panel is the single source of truth).';

    public function handle(): int
    {
        $baseline = config('access.baseline', []);
        $baselineValid = Permission::whereIn('name', $baseline)->pluck('name')->all();
        $designerPerms = config('access.designer_permissions', []);
        $designerValid = Permission::whereIn('name', $designerPerms)->pluck('name')->all();

        $users = User::with(['employee.designation', 'roles'])->whereHas('employee')->get();
        $updated = 0;

        foreach ($users as $user) {
            if (Access::isSuperAdmin($user)) {
                continue;
            }

            // Designation-based Designer grant (role-free).
            $isDesigner = str_contains(strtolower($user->employee?->designation?->name ?? ''), 'designer');
            $extra = $isDesigner ? $designerValid : [];
            $grants = array_values(array_unique(array_merge($baselineValid, $extra)));

            $hadRoles = $user->roles->isNotEmpty();
            $hadGrants = $user->hasAllPermissions($grants);

            DB::transaction(function () use ($user, $grants) {
                $user->givePermissionTo($grants);
                $user->roles()->detach();
            });

            if ($hadRoles || !$hadGrants) {
                $this->info(sprintf(
                    "User #%d %s: %s ensured, roles detached.",
                    $user->id,
                    $user->email,
                    $isDesigner ? 'designer+baseline' : 'baseline'
                ));
                $updated++;
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info("Done. {$updated} user(s) updated. Roles are deprecated; use the Access Control panel instead.");
        return Command::SUCCESS;
    }
}
