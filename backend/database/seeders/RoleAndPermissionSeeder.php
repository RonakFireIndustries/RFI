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

        // Create all permissions
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
            'purchase-order.view', 'purchase-order.create', 'purchase-order.edit', 'purchase-order.delete',
            'report.view', 'report.export',
            'dashboard.view',
            'payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.lock', 'payroll.pay', 'payroll.delete',
            'payslip.view', 'payslip.download',
            'salary-structure.view', 'salary-structure.create', 'salary-structure.edit', 'salary-structure.delete'
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // ==========================================
        // 1. Administration Department
        // ==========================================
        $superAdmin = Role::findOrCreate('Super Admin');
        $superAdmin->givePermissionTo(Permission::all());

        $systemAdmin = Role::findOrCreate('System Admin');
        $systemAdmin->givePermissionTo(Permission::all());

        $admin = Role::findOrCreate('Admin');
        $admin->givePermissionTo(Permission::all());

        $generalManager = Role::findOrCreate('General Manager');
        $generalManager->givePermissionTo(Permission::where('name', '!=', 'user.delete')->get());

        // ==========================================
        // 2. Production & Workshop
        // ==========================================
        $productionManager = Role::findOrCreate('Production Manager');
        $productionManager->givePermissionTo([
            'dashboard.view', 'attendance.view', 'inventory.view', 'product.view'
        ]);

        $workshopSupervisor = Role::findOrCreate('Workshop Supervisor');
        $workshopSupervisor->givePermissionTo(['dashboard.view', 'attendance.view']);

        $fitter = Role::findOrCreate('Fitter');
        $fitter->givePermissionTo(['dashboard.view', 'attendance.view', 'attendance.create']);

        $welder = Role::findOrCreate('Welder');
        $welder->givePermissionTo(['dashboard.view', 'attendance.view', 'attendance.create']);

        $electrician = Role::findOrCreate('Electrician');
        $electrician->givePermissionTo(['dashboard.view', 'attendance.view', 'attendance.create']);

        $helper = Role::findOrCreate('Helper');
        $helper->givePermissionTo(['dashboard.view', 'attendance.view', 'attendance.create']);

        // ==========================================
        // 3. Finance & Accounts
        // ==========================================
        $financeManager = Role::findOrCreate('Finance Manager');
        $financeManager->givePermissionTo([
            'dashboard.view', 'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.delete',
            'report.view', 'report.export', 'sales-order.view', 'purchase-order.view',
            'payroll.view', 'payroll.approve', 'payroll.lock', 'payroll.pay'
        ]);

        $accountant = Role::findOrCreate('Accountant');
        $accountant->givePermissionTo([
            'dashboard.view', 'invoice.view', 'invoice.create', 'invoice.edit', 'report.view'
        ]);

        // ==========================================
        // 4. Engineering & Design
        // ==========================================
        $designManager = Role::findOrCreate('Design Manager');
        $designManager->givePermissionTo(['dashboard.view', 'attendance.view', 'product.view']);

        $designer = Role::findOrCreate('Designer');
        $designer->givePermissionTo(['dashboard.view', 'attendance.view', 'product.view']);

        // ==========================================
        // 5. Inventory & Warehouse
        // ==========================================
        $storeManager = Role::findOrCreate('Store Manager');
        $storeManager->givePermissionTo([
            'dashboard.view', 'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'product.view'
        ]);

        // ==========================================
        // 6. Human Resources
        // ==========================================
        $hrManager = Role::findOrCreate('HR Manager');
        $hrManager->givePermissionTo([
            'dashboard.view', 'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete', 'report.view',
            'payroll.view', 'payroll.create', 'payroll.generate', 'payroll.delete',
            'payslip.view', 'payslip.download',
            'salary-structure.view', 'salary-structure.create', 'salary-structure.edit', 'salary-structure.delete'
        ]);

        // ==========================================
        // 7. Information Technology
        // ==========================================
        $itManager = Role::findOrCreate('IT Manager');
        $itManager->givePermissionTo([
            'dashboard.view', 'role.view', 'role.create', 'role.edit', 'role.delete',
            'permission.view', 'permission.create', 'permission.edit', 'permission.delete',
            'user.view', 'user.create', 'user.edit', 'user.delete'
        ]);
        
        // ==========================================
        // 8. Sales & Marketing (Missing in DB dump but added for completeness)
        // ==========================================
        $salesExecutive = Role::findOrCreate('Sales Executive');
        $salesExecutive->givePermissionTo([
            'dashboard.view', 'customer.view', 'customer.create', 'customer.edit',
            'sales-order.view', 'sales-order.create', 'sales-order.edit',
            'product.view'
        ]);
    }
}
