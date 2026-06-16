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

        $legacyPermissions = [
            'view dashboard', 'manage employees', 'view attendance',
            'view inventory', 'manage inventory', 'view products',
            'view categories', 'view warehouses', 'manage suppliers',
            'view invoices', 'view reports', 'manage settings',
            'manage payroll', 'manage sales', 'manage purchases',
            'create sales', 'manage transfers',
        ];

        $policyPermissions = [
            'view_employees', 'create_employees', 'update_employees', 'delete_employees',
            'view_products', 'create_products', 'update_products', 'delete_products',
            'view_customers', 'create_customers', 'update_customers', 'delete_customers',
            'view_suppliers', 'create_suppliers', 'update_suppliers', 'delete_suppliers',
            'view_sites', 'create_sites', 'update_sites', 'delete_sites',
            'view_departments', 'create_departments', 'update_departments', 'delete_departments',
            'view_designations', 'create_designations', 'update_designations', 'delete_designations',
            'view_categories', 'create_categories', 'update_categories', 'delete_categories',
            'view_attendance', 'create_attendance', 'update_attendance',
            'view_inventory', 'manage_inventory',
            'view_invoices', 'create_invoices', 'update_invoices', 'delete_invoices',
            'view_payments', 'create_payments',
            'view_payroll', 'manage_payroll',
            'view_leaves', 'create_leaves',
            'view_sales_orders', 'create_sales_orders',
            'view_purchase_orders', 'create_purchase_orders',
            'manage_roles', 'manage_permissions', 'manage_users',
        ];

        $permissions = array_values(array_unique(array_merge($legacyPermissions, $policyPermissions)));

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
            'manage inventory', 'view products', 'view reports',
            'view_employees', 'view_inventory', 'manage_inventory',
            'view_products', 'create_products', 'update_products',
        ]);
        
        Role::findByName('HR')->syncPermissions([
            'view dashboard', 'manage employees', 'view attendance',
            'manage payroll', 'view reports',
            'view_employees', 'create_employees', 'update_employees',
            'view_attendance', 'create_attendance', 'update_attendance',
            'view_payroll', 'manage_payroll',
            'view_departments', 'create_departments', 'update_departments',
            'view_designations', 'create_designations', 'update_designations',
            'view_leaves', 'create_leaves',
            'view_sites', 'create_sites', 'update_sites',
        ]);
        
        Role::findByName('Accountant')->syncPermissions([
            'view dashboard', 'view invoices', 'manage sales',
            'manage purchases', 'view reports',
            'view_invoices', 'create_invoices', 'update_invoices',
            'view_payments', 'create_payments',
            'view_customers', 'view_suppliers',
            'view_sales_orders', 'view_purchase_orders',
        ]);

        Role::findByName('Inventory Staff')->syncPermissions([
            'view dashboard', 'view inventory', 'manage inventory',
            'view products', 'manage transfers',
            'view_inventory', 'manage_inventory',
            'view_products', 'view_categories',
        ]);

        Role::findByName('Sales Executive')->syncPermissions([
            'view dashboard', 'create sales', 'view invoices',
            'view_customers', 'create_customers', 'update_customers',
            'view_sales_orders', 'create_sales_orders',
            'view_invoices', 'create_invoices',
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
