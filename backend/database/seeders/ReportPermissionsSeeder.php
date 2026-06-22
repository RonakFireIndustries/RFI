<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ReportPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionClass = app(\Spatie\Permission\Models\Permission::class);

        $permissions = [
            'view_reports',
            'create_reports',
            'edit_reports',
            'delete_reports',
            'export_reports',
            'schedule_reports',
        ];

        $created = [];
        foreach ($permissions as $perm) {
            $created[] = $permissionClass->firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($created);
        }

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($created);
        }
    }
}
