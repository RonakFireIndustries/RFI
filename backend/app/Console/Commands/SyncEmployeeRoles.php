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
    protected $description = '[Deprecated] Role-based access was removed. Ensures the baseline plus the config/access.php identity grants as direct permissions and detaches any lingering roles (Access Control panel is the single source of truth).';

    public function handle(): int
    {
        $baseline = config('access.baseline', []);
        $baselineValid = Permission::whereIn('name', $baseline)->pluck('name')->all();
        $retired = config('access.retired_baseline', []);
        $retiredValid = Permission::whereIn('name', $retired)->pluck('name')->all();

        // "Which department / designation gets what" lives in
        // config/access.php -> identity_grants. Flattened once here, then
        // filtered per group against the rows that actually exist.
        $groups = config('access.identity_grants', []);
        $groupPermissionNames = collect($groups)
            ->flatMap(fn(array $group) => $group['permissions'] ?? [])
            ->unique()
            ->values()
            ->all();
        $groupValid = Permission::whereIn('name', $groupPermissionNames)->pluck('name')->all();

        $users = User::with(['employee.designation', 'employee.department', 'roles'])->whereHas('employee')->get();
        $updated = 0;

        foreach ($users as $user) {
            if (Access::isSuperAdmin($user)) {
                continue;
            }

            // Which identity groups this employee belongs to, and what those
            // groups grant. Matching is case-insensitive and EXACT on both
            // designation and department - "Fitter" contains the letters "it",
            // so substring matching would silently promote every Fitter into
            // the IT group.
            $identity = Access::identityMatches($user);
            $matchedGroups = $identity['groups'];
            $extra = $identity['permissions'];

            // Only grant permission names whose rows exist, otherwise
            // givePermissionTo() throws on a typo'd or unseeded name.
            $extra = array_values(array_intersect(array_unique($extra), $groupValid));
            $grants = array_values(array_unique(array_merge($baselineValid, $extra)));

            $grantLabel = ($matchedGroups ? implode('+', $matchedGroups) . '+' : '') . 'baseline';

            $hadRoles = $user->roles->isNotEmpty();
            $hadGrants = $user->hasAllPermissions($grants);

            DB::transaction(function () use ($user, $grants, $retiredValid) {
                $user->givePermissionTo($grants);
                // Revoke permissions that have been retired from the baseline.
                // An identity grant in $grants WINS over the retire, otherwise
                // listing a permission in both places (attendance.view for HR,
                // payroll.view for Accounts) would have the grant handed out on
                // the line above and stripped again on this one - the order of
                // these two calls is the whole point.
                $revoke = array_values(array_diff($retiredValid, $grants));
                if ($revoke) {
                    $user->revokePermissionTo($revoke);
                }
                $user->roles()->detach();

                // Implied permissions must be applied INSIDE the transaction so
                // the guaranteed grants live on the same rows as the triggers.
                Access::applyImpliedPermissions($user);
            });

            if ($hadRoles || !$hadGrants) {
                $this->info(sprintf(
                    "User #%d %s: %s ensured, retired baseline revoked, roles detached.",
                    $user->id,
                    $user->email,
                    $grantLabel
                ));
                $updated++;
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info("Done. {$updated} user(s) updated. Roles are deprecated; use the Access Control panel instead.");
        return Command::SUCCESS;
    }
}
