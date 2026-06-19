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
            'site.view', 'site.create', 'site.edit', 'site.delete', 'site.assign', 'site.transfer', 'site.history.view',
            'department.view', 'department.create', 'department.edit', 'department.delete',
            'designation.view', 'designation.create', 'designation.edit', 'designation.delete',
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'employee.assign-manager', 'employee.assign-role',
            'view_categories', 'create_categories', 'update_categories', 'delete_categories',
            'view_attendance', 'create_attendance', 'update_attendance', 'delete_attendance',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete', 'attendance.checkin', 'attendance.checkout',
            'shift.view', 'shift.create', 'shift.edit', 'shift.delete',
            'view_inventory', 'manage_inventory',
            'view_invoices', 'create_invoices', 'update_invoices', 'delete_invoices',
            'view_payments', 'create_payments',
            'view_payroll', 'manage_payroll',
            'view_leaves', 'create_leaves',
            'view_sales_orders', 'create_sales_orders',
            'view_purchase_orders', 'create_purchase_orders',
            'manage_roles', 'manage_permissions', 'manage_users',
            'document.view', 'document.create', 'document.edit', 'document.delete',
            'document.download', 'document.preview', 'document.manage-expiry',
            'daily-report.view', 'daily-report.create', 'daily-report.edit', 'daily-report.delete',
            'daily-report.submit', 'daily-report.approve', 'daily-report.reject', 'daily-report.rework', 'daily-report.report.view',
            'leave.view', 'leave.create', 'leave.edit', 'leave.delete', 'leave.approve', 'leave.reject', 'leave.cancel', 'leave.balance.view',
            'leave-type.view', 'leave-type.create', 'leave-type.edit', 'leave-type.delete',
            'attendance.geo.checkin', 'attendance.geo.checkout', 'attendance.location.view', 'attendance.location.audit',
            'inventory.locations.view', 'inventory.locations.create', 'inventory.locations.edit', 'inventory.locations.delete',
            'inventory.units.view', 'inventory.units.create', 'inventory.units.edit', 'inventory.units.delete',
            'inventory.conversions.view', 'inventory.conversions.create', 'inventory.conversions.edit', 'inventory.conversions.delete',
            'inventory.stock.view', 'inventory.stock.create',
            'inventory.transactions.view', 'inventory.transactions.create',
            'inventory.requests.view', 'inventory.requests.create', 'inventory.requests.approve',
            'inventory.transfers.view', 'inventory.transfers.create', 'inventory.transfers.approve',
            'inventory.dashboard.view',
        ];

        $permissions = array_values(array_unique(array_merge($legacyPermissions, $policyPermissions)));

        $maxId = Permission::max('id') ?? 0;
        foreach ($permissions as $permission) {
            $exists = Permission::where('name', $permission)->first();
            if (!$exists) {
                $maxId++;
                Permission::insert(['id' => $maxId, 'name' => $permission, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $roles = [
            'Super Admin', 'Admin', 'Warehouse Manager', 
            'HR', 'Accountant', 'Inventory Staff', 'Sales Executive'
        ];

        $maxRoleId = Role::max('id') ?? 0;
        foreach ($roles as $role) {
            $exists = Role::where('name', $role)->first();
            if (!$exists) {
                $maxRoleId++;
                Role::insert(['id' => $maxRoleId, 'name' => $role, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        Role::findByName('Super Admin')->syncPermissions($permissions);
        Role::findByName('Admin')->syncPermissions($permissions);
        
        Role::findByName('Warehouse Manager')->syncPermissions([
            'view dashboard', 'manage employees', 'view inventory',
            'manage inventory', 'view products', 'view reports',
            'employee.view', 'view_inventory', 'manage_inventory',
            'view_products', 'create_products', 'update_products',
            'inventory.locations.view', 'inventory.locations.create', 'inventory.locations.edit', 'inventory.locations.delete',
            'inventory.units.view', 'inventory.units.create', 'inventory.units.edit', 'inventory.units.delete',
            'inventory.conversions.view', 'inventory.conversions.create', 'inventory.conversions.edit', 'inventory.conversions.delete',
            'inventory.stock.view', 'inventory.stock.create',
            'inventory.transactions.view', 'inventory.transactions.create',
            'inventory.requests.view', 'inventory.requests.create', 'inventory.requests.approve',
            'inventory.transfers.view', 'inventory.transfers.create', 'inventory.transfers.approve',
            'inventory.dashboard.view',
        ]);
        
        Role::findByName('HR')->syncPermissions([
            'view dashboard', 'manage employees', 'view attendance',
            'manage payroll', 'view reports',
            'employee.view', 'employee.create', 'employee.edit',
            'view_attendance', 'create_attendance', 'update_attendance', 'delete_attendance',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete', 'attendance.checkin', 'attendance.checkout',
            'shift.view', 'shift.create', 'shift.edit', 'shift.delete',
            'view_payroll', 'manage_payroll',
            'department.view', 'department.create', 'department.edit',
            'designation.view', 'designation.create', 'designation.edit',
            'view_leaves', 'create_leaves',
            'view_sites', 'create_sites', 'update_sites',
            'document.view', 'document.create', 'document.edit', 'document.delete',
            'document.download', 'document.preview', 'document.manage-expiry',
            'daily-report.view', 'daily-report.create', 'daily-report.edit', 'daily-report.delete',
            'daily-report.submit', 'daily-report.approve', 'daily-report.reject', 'daily-report.rework', 'daily-report.report.view',
        ]);
        
        Role::findByName('Accountant')->syncPermissions([
            'view dashboard', 'view invoices', 'manage sales',
            'manage purchases', 'view reports',
            'view_invoices', 'create_invoices', 'update_invoices',
            'view_payments', 'create_payments',
            'view_customers', 'view_suppliers',
            'view_sales_orders', 'view_purchase_orders',
            'view_payroll', 'manage_payroll', 'payroll.view', 'payroll.generate', 'salary-structure.view',
        ]);

        Role::findByName('Inventory Staff')->syncPermissions([
            'view dashboard', 'view inventory', 'manage inventory',
            'view products', 'manage transfers',
            'view_inventory', 'manage_inventory',
            'view_products', 'view_categories',
            'inventory.locations.view', 'inventory.units.view',
            'inventory.stock.view',
            'inventory.transactions.view', 'inventory.transactions.create',
            'inventory.requests.view', 'inventory.requests.create',
            'inventory.transfers.view', 'inventory.transfers.create',
            'inventory.dashboard.view',
        ]);

        Role::findByName('Sales Executive')->syncPermissions([
            'view dashboard', 'create sales', 'view invoices',
            'view_customers', 'create_customers', 'update_customers',
            'view_sales_orders', 'create_sales_orders',
            'view_invoices', 'create_invoices',
        ]);

        $maxBranchId = Branch::max('id') ?? 0;
        $branch = Branch::where('name', 'Headquarters')->first();
        if (!$branch) {
            $maxBranchId++;
            Branch::insert([
                'id' => $maxBranchId,
                'name' => 'Headquarters',
                'location' => 'Main City',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $branch = Branch::find($maxBranchId);
        }

        $maxUserId = User::max('id') ?? 0;

        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        if (!$superAdmin) {
            $maxUserId++;
            User::insert([
                'id' => $maxUserId,
                'email' => 'superadmin@example.com',
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $superAdmin = User::find($maxUserId);
        }
        $superAdmin->assignRole('Super Admin');

        $admin = User::where('email', 'admin@erp.com')->first();
        if (!$admin) {
            $maxUserId++;
            User::insert([
                'id' => $maxUserId,
                'email' => 'admin@erp.com',
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $admin = User::find($maxUserId);
        }
        $admin->assignRole('Admin');
    }
}
