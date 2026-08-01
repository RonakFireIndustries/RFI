<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BuildingPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = ['building.view', 'building.create', 'building.edit', 'building.delete'];

        $maxId = Permission::max('id') ?? 0;
        foreach ($permissions as $permission) {
            $exists = Permission::where('name', $permission)->first();
            if (!$exists) {
                $maxId++;
                Permission::insert([
                    'id' => $maxId,
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $admin = Role::findByName('Admin');
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        $manager = Role::findByName('Manager');
        if ($manager) {
            $manager->givePermissionTo('building.view');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
