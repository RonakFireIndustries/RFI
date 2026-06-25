<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
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

        // Remove old replaced roles (detach permissions first)
        $oldRolesToRemove = ['Super Admin', 'Warehouse Manager', 'Inventory Staff', 'Sales Executive'];
        foreach ($oldRolesToRemove as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->permissions()->detach();
                $role->delete();
            }
        }

        // Define new roles
        $newRoles = ['Admin', 'Manager', 'Store Manager', 'Accountant', 'HR', 'Employee'];

        $maxRoleId = Role::max('id') ?? 0;
        foreach ($newRoles as $role) {
            $exists = Role::where('name', $role)->first();
            if (!$exists) {
                $maxRoleId++;
                Role::insert(['id' => $maxRoleId, 'name' => $role, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        // Attendance write permissions - Admin should NOT have these
        $attendanceWritePermissions = [
            'create_attendance', 'update_attendance', 'delete_attendance',
            'attendance.create', 'attendance.edit', 'attendance.delete',
            'attendance.checkin', 'attendance.checkout',
            'attendance.geo.checkin', 'attendance.geo.checkout',
        ];

        $allPermissions = Permission::pluck('name')->toArray();
        $adminPermissions = array_values(array_diff($allPermissions, $attendanceWritePermissions));

        Role::findByName('Admin')->syncPermissions($adminPermissions);

        // Manager: can view sites, their stock, and site employees (read-only)
        Role::findByName('Manager')->syncPermissions([
            'view dashboard',
            'view_sites', 'site.view', 'view_employees',
            'employee.view',
            'view_products', 'view_categories',
            'view_inventory', 'inventory.stock.view', 'inventory.dashboard.view',
            'inventory.locations.view', 'inventory.transactions.view', 'inventory.transfers.view',
            'inventory.units.view', 'inventory.requests.view',
            'daily-report.view', 'daily-report.report.view',
            'attendance.view', 'view_attendance',
            'leave.view',
            'view reports',
        ]);

        // Store Manager: full inventory operations
        Role::findByName('Store Manager')->syncPermissions([
            'view dashboard', 'manage transfers',
            'view_inventory', 'manage_inventory',
            'view_products', 'create_products', 'update_products',
            'view_categories', 'create_categories', 'update_categories',
            'inventory.locations.view', 'inventory.locations.create', 'inventory.locations.edit',
            'inventory.units.view', 'inventory.units.create', 'inventory.units.edit',
            'inventory.conversions.view', 'inventory.conversions.create', 'inventory.conversions.edit',
            'inventory.stock.view', 'inventory.stock.create',
            'inventory.transactions.view', 'inventory.transactions.create',
            'inventory.requests.view', 'inventory.requests.create', 'inventory.requests.approve',
            'inventory.transfers.view', 'inventory.transfers.create', 'inventory.transfers.approve',
            'inventory.dashboard.view',
            'employee.view',
            'view_products', 'view_categories',
            'view_purchase_orders', 'create_purchase_orders',
            'view_sales_orders', 'create_sales_orders',
        ]);

        // Accountant: payroll + purchase/sales orders (no inventory update)
        Role::findByName('Accountant')->syncPermissions([
            'view dashboard', 'view reports',
            'view_payroll', 'manage_payroll',
            'view_purchase_orders', 'create_purchase_orders',
            'view_sales_orders', 'create_sales_orders',
            'view_invoices', 'create_invoices', 'update_invoices',
            'view_payments', 'create_payments',
            'view_customers', 'view_suppliers',
            'employee.view', 'view_employees',
                    'view_products', 'view_categories',
                    'salary-structure.view',
        ]);

        // HR: unchanged
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
            'leave.view', 'leave.create', 'leave.approve', 'leave.reject', 'leave.balance.view',
            'leave-type.view', 'leave-type.create', 'leave-type.edit',
        ]);

        // Employee: self-service (own attendance, daily reports, payroll, leaves)
        Role::findByName('Employee')->syncPermissions([
            'view dashboard',
            'attendance.checkin', 'attendance.checkout',
            'attendance.view',
            'daily-report.create', 'daily-report.view', 'daily-report.submit',
            'leave.view', 'leave.create', 'leave.cancel', 'leave.balance.view',
            'view_payroll',
        ]);

        $maxUserId = User::max('id') ?? 0;

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
