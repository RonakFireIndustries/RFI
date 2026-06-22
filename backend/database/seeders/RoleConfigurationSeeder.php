<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionScope;
use App\Models\RoleTemplate;
use App\Models\SystemException;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $scopes = [
            ['name' => 'self', 'label' => 'Self', 'description' => 'Own records only', 'level' => 0],
            ['name' => 'team', 'label' => 'Team', 'description' => 'Team members', 'level' => 1],
            ['name' => 'department', 'label' => 'Department', 'description' => 'Entire department', 'level' => 2],
            ['name' => 'branch', 'label' => 'Branch', 'description' => 'Branch-wide', 'level' => 3],
            ['name' => 'region', 'label' => 'Region', 'description' => 'Regional access', 'level' => 4],
            ['name' => 'organization', 'label' => 'Organization', 'description' => 'Entire organization', 'level' => 5],
            ['name' => 'global', 'label' => 'Global', 'description' => 'All access', 'level' => 6],
        ];
        foreach ($scopes as $s) {
            PermissionScope::firstOrCreate(['name' => $s['name']], $s);
        }

        $templates = [
            [
                'name' => 'Manager Template',
                'description' => 'Comprehensive access for department managers with read/write on most modules',
                'category' => 'management',
                'permissions' => ['view_dashboard', 'view_employees', 'view_products', 'view_customers', 'view_suppliers', 'view_sites', 'view_inventory', 'view_invoices', 'view_reports', 'view_attendance', 'view_leaves'],
            ],
            [
                'name' => 'Executive Template',
                'description' => 'Read-only access across all modules for executive review',
                'category' => 'executive',
                'permissions' => ['view_dashboard', 'view_employees', 'view_products', 'view_customers', 'view_suppliers', 'view_sites', 'view_inventory', 'view_invoices', 'view_reports', 'view_attendance', 'view_payroll', 'view_purchase_orders', 'view_sales_orders'],
            ],
            [
                'name' => 'Finance Template',
                'description' => 'Financial module access including invoices, payments, and payroll',
                'category' => 'finance',
                'permissions' => ['view_dashboard', 'view_invoices', 'create_invoices', 'update_invoices', 'view_payments', 'create_payments', 'view_payroll', 'manage_payroll', 'view_customers', 'view_suppliers', 'view_reports'],
            ],
            [
                'name' => 'Operations Template',
                'description' => 'Operational access for inventory, purchasing, and site management',
                'category' => 'operations',
                'permissions' => ['view_dashboard', 'view_inventory', 'manage_inventory', 'view_products', 'view_categories', 'view_purchase_orders', 'create_purchase_orders', 'view_sites', 'view_suppliers', 'inventory.stock.view', 'inventory.transactions.view', 'inventory.transactions.create', 'inventory.transfers.view', 'inventory.transfers.create'],
            ],
        ];
        foreach ($templates as $t) {
            RoleTemplate::firstOrCreate(['name' => $t['name']], $t);
        }

        $exceptions = [
            [
                'name' => 'Override Credit Limits',
                'key' => 'override_credit_limits',
                'description' => 'Allow role to override customer credit limits during order processing',
                'category' => 'financial',
            ],
            [
                'name' => 'Approve Beyond Threshold',
                'key' => 'approve_beyond_threshold',
                'description' => 'Allow approval of transactions exceeding standard approval thresholds',
                'category' => 'financial',
            ],
            [
                'name' => 'Emergency Inventory Release',
                'key' => 'emergency_inventory_release',
                'description' => 'Authorize emergency release of inventory without standard approvals',
                'category' => 'inventory',
            ],
            [
                'name' => 'Bulk Data Export',
                'key' => 'bulk_data_export',
                'description' => 'Allow export of large data sets from the system',
                'category' => 'system',
            ],
            [
                'name' => 'Payroll Override',
                'key' => 'payroll_override',
                'description' => 'Override payroll calculations for adjustments',
                'category' => 'financial',
            ],
            [
                'name' => 'Financial Adjustments',
                'key' => 'financial_adjustments',
                'description' => 'Make manual adjustments to financial records',
                'category' => 'financial',
            ],
        ];
        foreach ($exceptions as $e) {
            SystemException::firstOrCreate(['key' => $e['key']], $e);
        }

        $deleteDep = DB::table('permissions')->where('name', 'delete_employees')->value('id');
        $editDep = DB::table('permissions')->where('name', 'edit_employees')->value('id');
        $createDep = DB::table('permissions')->where('name', 'create_employees')->value('id');
        $viewDep = DB::table('permissions')->where('name', 'view_employees')->value('id');
        if ($deleteDep && $editDep) {
            DB::table('permission_dependencies')->upsert(
                [
                    ['permission_id' => $deleteDep, 'depends_on_permission_id' => $editDep, 'type' => 'requires'],
                    ['permission_id' => $editDep, 'depends_on_permission_id' => $createDep, 'type' => 'requires'],
                    ['permission_id' => $createDep, 'depends_on_permission_id' => $viewDep, 'type' => 'requires'],
                ],
                ['permission_id', 'depends_on_permission_id'],
                ['type']
            );
        }
    }
}
