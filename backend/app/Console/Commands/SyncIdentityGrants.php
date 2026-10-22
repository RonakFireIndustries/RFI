<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Access;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Apply the config/access.php identity grants to the matching employees.
 *
 * `employees:sync-roles` does this too, but it also detaches every Spatie role
 * and revokes the retired baseline. That migration is deliberate and should
 * happen, but it is a separate concern: doing it in order to ship one new
 * module would silently strip the roles that other code still reads (the
 * role-based sales-price gate in ProductResource, the role-based dashboard
 * resolution in DashboardService).
 *
 * This command only ADDS the identity grants as direct permissions. It never
 * revokes anything and never touches a role, so it is safe to run the moment
 * a module is added to config/access.php.
 */
class SyncIdentityGrants extends Command
{
    protected $signature = 'access:sync-grants
                            {--module= : Only sync permissions for this module prefix (e.g. holidays)}
                            {--dry-run : Report what would change without applying}';

    protected $description = 'Apply the config/access.php identity grants to matching employees as direct permissions (additive only - no roles touched, nothing revoked).';

    public function handle(): int
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $dryRun = (bool) $this->option('dry-run');
        $moduleFilter = $this->option('module');

        $groups = config('access.identity_grants', []);
        if (!is_array($groups) || $groups === []) {
            $this->error('No identity_grants found in config/access.php.');
            return self::FAILURE;
        }

        // Only grant names whose rows exist, otherwise givePermissionTo() throws
        // on a name that has not been seeded yet.
        $groupPermissionNames = collect($groups)
            ->flatMap(fn(array $group) => $group['permissions'] ?? [])
            ->unique()
            ->values()
            ->all();

        if ($moduleFilter) {
            $groupPermissionNames = array_values(array_filter(
                $groupPermissionNames,
                fn(string $name) => str_starts_with($name, $moduleFilter . '.')
            ));
        }

        $valid = \Spatie\Permission\Models\Permission::whereIn('name', $groupPermissionNames)
            ->pluck('name')
            ->all();

        if ($valid === []) {
            $this->warn('No matching permission rows in the database. Run: php artisan db:seed --class=CanonicalPermissionsSeeder');
            return self::SUCCESS;
        }

        $users = User::with(['employee.designation', 'employee.department'])->whereHas('employee')->get();
        $changed = 0;
        $unchanged = 0;

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
            $granted = array_values(array_intersect($identity['permissions'], $valid));
            if ($granted === []) {
                $unchanged++;
                continue;
            }

            $label = $matchedGroups ? implode('+', $matchedGroups) : 'identity';
            $missing = array_values(array_diff($granted, $user->getDirectPermissions()->pluck('name')->all()));

            if ($missing === []) {
                $unchanged++;
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    'User #%d %s [%s]: would add %s',
                    $user->id,
                    $user->email,
                    $label,
                    implode(', ', $missing)
                ));
                $changed++;
                continue;
            }

            DB::transaction(function () use ($user, $granted) {
                $user->givePermissionTo($granted);
            });

            $this->line(sprintf(
                'User #%d %s [%s]: added %s',
                $user->id,
                $user->email,
                $label,
                implode(', ', $missing)
            ));
            $changed++;
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info(sprintf(
            '%s %d user(s) updated, %d already correct.',
            $dryRun ? 'Dry run:' : 'Done.',
            $changed,
            $unchanged
        ));

        return self::SUCCESS;
    }
}
