<?php

namespace App\Support;

use App\Models\User;

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
}
