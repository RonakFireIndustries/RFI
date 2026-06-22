<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportCategory;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        ReportCategory::truncate();
        Report::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $admin = User::first();

        $categories = [
            ['name' => 'Sales', 'slug' => 'sales', 'description' => 'Sales performance and revenue reports', 'icon' => 'TrendingUp', 'sort_order' => 1],
            ['name' => 'Inventory', 'slug' => 'inventory', 'description' => 'Stock levels, movements, and valuation reports', 'icon' => 'Package', 'sort_order' => 2],
            ['name' => 'Financial', 'slug' => 'financial', 'description' => 'Payments, invoices, and accounting reports', 'icon' => 'DollarSign', 'sort_order' => 3],
            ['name' => 'Employees', 'slug' => 'employees', 'description' => 'Employee information and HR metrics', 'icon' => 'Users', 'sort_order' => 4],
            ['name' => 'Attendance', 'slug' => 'attendance', 'description' => 'Employee attendance and timesheet reports', 'icon' => 'Clock', 'sort_order' => 5],
            ['name' => 'Payroll', 'slug' => 'payroll', 'description' => 'Salary, deductions, and payroll summary reports', 'icon' => 'Wallet', 'sort_order' => 6],
            ['name' => 'Purchases', 'slug' => 'purchases', 'description' => 'Procurement and supplier reports', 'icon' => 'ShoppingBag', 'sort_order' => 7],
            ['name' => 'Leaves', 'slug' => 'leaves', 'description' => 'Leave balances, requests, and utilization reports', 'icon' => 'Calendar', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            $category = ReportCategory::create($cat);

            $reports = match ($cat['slug']) {
                'sales' => [
                    ['name' => 'Sales Overview', 'slug' => 'sales-overview', 'description' => 'Summary of sales orders, revenue, and trends', 'route' => '/dashboard/reports/sales', 'api_endpoint' => '/api/v1/reports/sales', 'sort_order' => 1],
                    ['name' => 'Sales by Customer', 'slug' => 'sales-by-customer', 'description' => 'Sales breakdown by customer account', 'sort_order' => 2],
                    ['name' => 'Revenue Report', 'slug' => 'revenue-report', 'description' => 'Revenue analysis with period comparisons', 'sort_order' => 3],
                ],
                'inventory' => [
                    ['name' => 'Stock Summary', 'slug' => 'stock-summary', 'description' => 'Current stock levels across all locations', 'route' => '/dashboard/inventory/stock', 'sort_order' => 1],
                    ['name' => 'Stock Movement', 'slug' => 'stock-movement', 'description' => 'Transaction history and stock transfers', 'route' => '/dashboard/inventory/transactions', 'sort_order' => 2],
                    ['name' => 'Inventory Valuation', 'slug' => 'inventory-valuation', 'description' => 'Stock valuation by product and location', 'sort_order' => 3],
                ],
                'financial' => [
                    ['name' => 'Payment Report', 'slug' => 'payment-report', 'description' => 'Payment collections and outstanding amounts', 'api_endpoint' => '/api/v1/reports/payments', 'sort_order' => 1],
                    ['name' => 'Invoice Summary', 'slug' => 'invoice-summary', 'description' => 'Invoice generation and payment status', 'route' => '/dashboard/invoices', 'sort_order' => 2],
                ],
                'employees' => [
                    ['name' => 'Employee Directory', 'slug' => 'employee-directory', 'description' => 'Complete employee list with department and designation', 'api_endpoint' => '/api/v1/reports/employees', 'route' => '/dashboard/employees', 'sort_order' => 1],
                    ['name' => 'Department Overview', 'slug' => 'department-overview', 'description' => 'Employees grouped by department', 'route' => '/dashboard/departments', 'sort_order' => 2],
                    ['name' => 'Employee Count Report', 'slug' => 'employee-count-report', 'description' => 'Employee count and distribution analysis', 'api_endpoint' => '/api/v1/reports/employees', 'sort_order' => 3],
                ],
                'attendance' => [
                    ['name' => 'Attendance Report', 'slug' => 'attendance-report', 'description' => 'Monthly attendance records by employee', 'api_endpoint' => '/api/v1/reports/attendance', 'route' => '/dashboard/attendance', 'sort_order' => 1],
                    ['name' => 'Attendance Summary', 'slug' => 'attendance-summary', 'description' => 'Present, absent, late, and half-day counts', 'api_endpoint' => '/api/v1/reports/attendance', 'sort_order' => 2],
                ],
                'payroll' => [
                    ['name' => 'Payroll Summary', 'slug' => 'payroll-summary', 'description' => 'Payroll processing summary and totals', 'route' => '/dashboard/payroll', 'sort_order' => 1],
                    ['name' => 'Salary Structure Report', 'slug' => 'salary-structure', 'description' => 'Employee salary components breakdown', 'route' => '/dashboard/salary-structures', 'sort_order' => 2],
                ],
                'purchases' => [
                    ['name' => 'Purchase Orders', 'slug' => 'purchase-orders', 'description' => 'Purchase order status and amounts', 'route' => '/dashboard/purchases', 'sort_order' => 1],
                    ['name' => 'Supplier Report', 'slug' => 'supplier-report', 'description' => 'Supplier performance and procurement metrics', 'sort_order' => 2],
                ],
                'leaves' => [
                    ['name' => 'Leave Report', 'slug' => 'leave-report', 'description' => 'Leave requests by status and period', 'api_endpoint' => '/api/v1/reports/leaves', 'route' => '/dashboard/leave-management', 'sort_order' => 1],
                    ['name' => 'Leave Balance', 'slug' => 'leave-balance', 'description' => 'Employee leave balance overview', 'route' => '/dashboard/leave-management/balances', 'sort_order' => 2],
                ],
                default => [],
            };

            foreach ($reports as $i => $reportData) {
                $reportData['category_id'] = $category->id;
                $reportData['created_by'] = $admin?->id;
                $reportData['status'] = 'active';
                Report::create($reportData);
            }
        }
    }
}
