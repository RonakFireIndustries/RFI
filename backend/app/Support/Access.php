<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Access
{
    /**
     * Get the raw module definition map from config/access.php
     */
    public static function categories(): array
    {
        return config('access.categories', []);
    }

    /**
     * Flatten every module across all categories.
     *
     * @return array<string, array{label:string, actions:array<int,string>}>
     */
    public static function modules(): array
    {
        $modules = [];
        foreach (static::categories() as $category) {
            foreach ($category as $key => $def) {
                $modules[$key] = $def;
            }
        }
        return $modules;
    }

    /**
     * Canonical permission name for a module + action.
     */
    public static function permissionName(string $module, string $action): string
    {
        return "{$module}.{$action}";
    }

    /**
     * All canonical permission names defined by the config.
     *
     * @return array<int, string>
     */
    public static function allPermissionNames(): array
    {
        $names = [];
        foreach (static::modules() as $module => $def) {
            foreach ($def['actions'] as $action) {
                $names[] = static::permissionName($module, $action);
            }
        }
        return array_values(array_unique($names));
    }

    /**
     * Legacy name → canonical name map from config/access.php
     */
    public static function legacyAliases(): array
    {
        return config('access.legacy_aliases', []);
    }

    /**
     * Check whether a user has a specific canonical permission.
     *
     * Includes an exemption for users who are effectively super admins
     * (Admin role) — they can do everything.
     */
    public static function can(User $user, string $permission): bool
    {
        return static::isSuperAdmin($user) || $user->hasPermissionTo($permission);
    }

    /**
     * A user is a super admin when they hold the Admin role (or are explicitly
     * flagged). Super admins bypass all permission checks.
     */
    public static function isSuperAdmin(User $user): bool
    {
        if ($user->is_super_admin ?? false) {
            return true;
        }
        return $user->hasRole('Admin') || $user->hasRole('Super Admin');
    }

    /**
     * The identity groups an employee belongs to, and the permissions those
     * groups grant.
     *
     * "Which department / designation gets what" lives in
     * config/access.php -> identity_grants. Matching is case-insensitive and
     * EXACT on both designation and department - "Fitter" contains the
     * letters "it", so substring matching would silently promote every Fitter
     * into the IT group.
     *
     * @return array{groups: array<int, string>, permissions: array<int, string>}
     */
    public static function identityMatches(User $user): array
    {
        $groups = config('access.identity_grants', []);

        $designation = static::identityName($user, 'designation');
        $department = static::identityName($user, 'department');

        $matched = [];
        $permissions = [];

        foreach ($groups as $label => $group) {
            $designations = array_map(
                fn($name) => strtolower(trim((string) $name)),
                $group['designations'] ?? []
            );
            $departments = array_map(
                fn($name) => strtolower(trim((string) $name)),
                $group['departments'] ?? []
            );

            if (
                in_array($designation, $designations, true)
                || in_array($department, $departments, true)
            ) {
                $matched[] = (string) $label;
                $permissions = array_merge($permissions, $group['permissions'] ?? []);
            }
        }

        return [
            'groups' => $matched,
            'permissions' => array_values(array_unique($permissions)),
        ];
    }

    /**
     * The name of an employee's designation or department, lowercased.
     *
     * The relation is read through getRelationValue() rather than
     * $employee->department, because the employees table also carries a
     * `department` string column from the original create_employees_table
     * migration and an attribute of the same name shadows the relation. On
     * any install that still has that column - which is every fresh one - the
     * department match would silently never match anything.
     */
    private static function identityName(User $user, string $relation): string
    {
        $employee = $user->employee;
        if (!$employee) {
            return '';
        }

        $model = $employee->getRelationValue($relation);
        if (!$model instanceof Model) {
            return '';
        }

        return strtolower(trim((string) $model->name));
    }

/**
     * All canonical permission names a user has, as plain strings
     * (union of direct + role-derived, deduped).
     *
     * @return array<int, string>
     */
    public static function permissionNamesFor(User $user): array
    {
        if (static::isSuperAdmin($user)) {
            return static::allPermissionNames();
        }

        return $user->getAllPermissions()
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Auto-grant implied permissions (see config('access.implied_permissions')).
     *
     * If the user holds ANY permission from one of a grant's trigger modules,
     * the corresponding implied permission (e.g. suppliers.view) is granted.
     * Implied permissions are only ever added, never revoked.
     *
     * @return array<int, string> the permission names newly granted by this pass
     */
    public static function applyImpliedPermissions(User $user): array
    {
        if (static::isSuperAdmin($user)) {
            return [];
        }

        $implied = config('access.implied_permissions', []);
        if (!$implied) {
            return [];
        }

        $modules = static::modules();
        $held = $user->getAllPermissions()->pluck('name')->all();
        $newlyGranted = [];

        foreach ($implied as $grantName => $triggerModules) {
            if ($user->hasPermissionTo($grantName)) {
                continue;
            }

            $triggers = [];
            foreach ($triggerModules as $module) {
                foreach ($modules[$module]['actions'] ?? [] as $action) {
                    $triggers[] = static::permissionName($module, $action);
                }
            }

            if (array_intersect($triggers, $held) && static::ensurePermission($grantName)) {
                $user->givePermissionTo($grantName);
                $newlyGranted[] = $grantName;
            }
        }

        return $newlyGranted;
    }

    /**
     * Make sure a canonical permission row exists so it can be granted.
     *
     * config/access.php is the source of truth for the canonical names, so a
     * module added there (e.g. `boq-products.view`) must be grantable even
     * before CanonicalPermissionsSeeder has been re-run on this environment.
     * Non-canonical names are left alone — a stale name must not create rows.
     *
     * @return bool true when the permission exists (and is therefore grantable)
     */
    public static function ensurePermission(string $name): bool
    {
        if (Permission::where('name', $name)->exists()) {
            return true;
        }

        if (!in_array($name, static::allPermissionNames(), true)) {
            return false;
        }

        Permission::findOrCreate($name, 'web');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return true;
    }
}
