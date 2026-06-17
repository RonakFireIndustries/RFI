<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'permission.view', 'permission.create', 'permission.edit', 'permission.delete',
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            'sales-order.view', 'sales-order.create', 'sales-order.edit', 'sales-order.delete',
            'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.delete',
            'report.view', 'report.export',
            'dashboard.view'
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Create roles and assign created permissions
        $superAdmin = Role::findOrCreate('Super Admin');
        // Super Admin gets all permissions via Gate::before rule in AuthServiceProvider, 
        // but we'll assign them directly here as requested.
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::findOrCreate('Admin');
        $admin->givePermissionTo([
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'dashboard.view',
            'report.view', 'report.export',
            'inventory.view', 'warehouse.view', 'product.view', 'customer.view', 'sales-order.view', 'invoice.view'
        ]);

        $manager = Role::findOrCreate('Manager');
        $manager->givePermissionTo([
            'employee.view',
            'dashboard.view',
            'report.view',
            'inventory.view', 'warehouse.view', 'product.view', 'customer.view', 'sales-order.view', 'invoice.view'
        ]);

        $hr = Role::findOrCreate('HR');
        $hr->givePermissionTo([
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
            'dashboard.view', 'report.view'
        ]);

        $employee = Role::findOrCreate('Employee');
        $employee->givePermissionTo([
            'dashboard.view', 'attendance.view', 'attendance.create'
        ]);

        $accountant = Role::findOrCreate('Accountant');
        $accountant->givePermissionTo([
            'dashboard.view', 'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.delete', 'report.view', 'report.export'
        ]);

        $warehouseManager = Role::findOrCreate('Warehouse Manager');
        $warehouseManager->givePermissionTo([
            'dashboard.view', 'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'product.view'
        ]);

        $inventoryStaff = Role::findOrCreate('Inventory Staff');
        $inventoryStaff->givePermissionTo([
            'dashboard.view', 'inventory.view', 'inventory.create', 'inventory.edit', 'product.view'
        ]);

        $salesExecutive = Role::findOrCreate('Sales Executive');
        $salesExecutive->givePermissionTo([
            'dashboard.view', 'customer.view', 'customer.create', 'customer.edit',
            'sales-order.view', 'sales-order.create', 'sales-order.edit',
            'product.view'
        ]);
    }
}
