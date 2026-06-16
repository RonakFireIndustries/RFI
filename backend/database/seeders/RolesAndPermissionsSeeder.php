<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard', 'manage employees', 'view attendance', 
            'view inventory', 'manage inventory', 'view products', 
            'view categories', 'view warehouses', 'manage suppliers', 
            'view invoices', 'view reports', 'manage settings', 
            'manage payroll', 'manage sales', 'manage purchases', 
            'create sales', 'manage transfers'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'Super Admin', 'Admin', 'Warehouse Manager', 
            'HR', 'Accountant', 'Inventory Staff', 'Sales Executive'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Role::findByName('Super Admin')->syncPermissions($permissions);
        Role::findByName('Admin')->syncPermissions($permissions);
        
        Role::findByName('Warehouse Manager')->syncPermissions([
            'view dashboard', 'manage employees', 'view inventory', 
            'manage inventory', 'view products', 'view reports'
        ]);
        
        Role::findByName('HR')->syncPermissions([
            'view dashboard', 'manage employees', 'view attendance', 
            'manage payroll', 'view reports'
        ]);
        
        Role::findByName('Accountant')->syncPermissions([
            'view dashboard', 'view invoices', 'manage sales', 
            'manage purchases', 'view reports'
        ]);

        Role::findByName('Inventory Staff')->syncPermissions([
            'view dashboard', 'view inventory', 'manage inventory', 
            'view products', 'manage transfers'
        ]);

        Role::findByName('Sales Executive')->syncPermissions([
            'view dashboard', 'create sales', 'view invoices'
        ]);

        $branch = Branch::firstOrCreate([
            'name' => 'Headquarters',
            'location' => 'Main City',
        ]);

        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'branch_id' => $branch->id,
        ]);
        $superAdmin->assignRole('Super Admin');

        $admin = User::firstOrCreate([
            'email' => 'admin@erp.com',
        ], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'branch_id' => $branch->id,
        ]);
        $admin->assignRole('Admin');
    }
}
