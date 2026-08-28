<?php

namespace Database\Seeders;

use App\Support\Access;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class CanonicalPermissionsSeeder extends Seeder
{
    /**
     * Create the canonical "{module}.{action}" permissions from config/access.php
     * and replicate any existing grants that were stored under legacy names.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $canonicalNames = Access::allPermissionNames();

        $maxId = Permission::max('id') ?? 0;
        foreach ($canonicalNames as $name) {
            $exists = Permission::where('name', $name)->first();
            if (!$exists) {
                $maxId++;
                DB::table('permissions')->insert([
                    'id' => $maxId,
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->copyLegacyGrants();
    }

    /**
     * For every legacy permission name that maps to a canonical name, transfer
     * the grants found in model_has_permissions and role_has_permissions to the
     * canonical row (without removing the legacy rows).
     */
    protected function copyLegacyGrants(): void
    {
        $aliases = Access::legacyAliases();
        $morphKey = config('permission.column_names.model_morph_key', 'model_morph_key');

        foreach ($aliases as $legacyName => $canonicalName) {
            $legacyPerm = Permission::where('name', $legacyName)->first();
            $canonicalPerm = Permission::where('name', $canonicalName)->first();
            if (!$legacyPerm || !$canonicalPerm) {
                continue;
            }

            // Copy model_has_permissions grants (per-user direct)
            $userGrants = DB::table('model_has_permissions')
                ->where('permission_id', $legacyPerm->id)
                ->get(['model_type', $morphKey]);

            foreach ($userGrants as $g) {
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
                }
            }

            // Copy role_has_permissions grants
            $roleGrants = DB::table('role_has_permissions')
                ->where('permission_id', $legacyPerm->id)
                ->get(['role_id']);

            foreach ($roleGrants as $g) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $canonicalPerm->id)
                    ->where('role_id', $g->role_id)
                    ->exists();
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $canonicalPerm->id,
                        'role_id' => $g->role_id,
                    ]);
                }
            }
        }
    }
}
