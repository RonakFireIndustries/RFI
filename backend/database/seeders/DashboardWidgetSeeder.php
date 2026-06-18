<?php

namespace Database\Seeders;

use App\Models\DashboardWidget;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DashboardWidgetSeeder extends Seeder
{
    protected $designationMap = [];

    public function run(): void
    {
        $this->designationMap = Designation::pluck('id', 'name')->toArray();

        $this->seedCards();
        $this->seedCharts();
        $this->seedQuickActions();
        $this->seedAlerts();
    }

    protected function widget(array $data, array $designationNames): void
    {
        $designationIds = collect($designationNames)
            ->map(fn($name) => $this->designationMap[$name] ?? null)
            ->filter()
            ->values()
            ->toArray();

        if (empty($designationIds)) return;

        $widget = DashboardWidget::create([
            'widget_key' => $data['widget_key'],
            'name' => $data['name'],
            'type' => $data['type'],
            'icon' => $data['icon'] ?? null,
            'chart_type' => $data['chart_type'] ?? null,
            'config' => $data['config'] ?? null,
            'permission' => $data['permission'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);

        $widget->designations()->sync($designationIds);
    }

    protected function all(): array
    {
        return [
            'Super Admin', 'System Admin', 'Admin', 'General Manager',
            'Production Manager', 'Workshop Supervisor',
            'Fitter', 'Welder', 'Electrician', 'Helper',
            'Finance Manager', 'Accountant',
            'Design Manager', 'Designer',
            'Store Manager', 'HR Manager', 'IT Manager',
        ];
    }

    protected function exec(): array { return ['Super Admin', 'System Admin', 'Admin']; }
    protected function execGm(): array { return ['Super Admin', 'System Admin', 'Admin', 'General Manager']; }
    protected function execGmHr(): array { return ['Super Admin', 'System Admin', 'Admin', 'General Manager', 'HR Manager']; }
    protected function execAllMgmt(): array { return ['Super Admin', 'System Admin', 'Admin', 'General Manager', 'Production Manager', 'Finance Manager', 'HR Manager', 'Design Manager', 'Store Manager']; }
    protected function workers(): array { return ['Fitter', 'Welder', 'Electrician', 'Helper']; }
    protected function fieldWorkers(): array { return ['Fitter', 'Welder', 'Electrician', 'Helper', 'Workshop Supervisor']; }
    protected function allWorkers(): array { return ['Fitter', 'Welder', 'Electrician', 'Helper', 'Workshop Supervisor', 'Designer']; }
    protected function finance(): array { return ['Finance Manager', 'Accountant']; }
    protected function financeExec(): array { return ['Finance Manager', 'Accountant', 'Admin', 'Super Admin']; }

    // ─── CARDS ─────────────────────────────────────────────────────

    protected function seedCards(): void
    {
        $order = 0;

        $this->widget([
            'widget_key' => 'total_employees', 'name' => 'Total Employees', 'type' => 'card', 'icon' => 'Users',
            'permission' => 'employee.view', 'order' => ++$order,
        ], $this->execGmHr());

        $this->widget([
            'widget_key' => 'present_today', 'name' => 'Present Today', 'type' => 'card', 'icon' => 'UserCheck',
            'permission' => 'attendance.view', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'General Manager', 'Production Manager', 'HR Manager']);

        $this->widget([
            'widget_key' => 'absent_today', 'name' => 'Absent Today', 'type' => 'card', 'icon' => 'UserX',
            'permission' => 'attendance.view', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'General Manager', 'Production Manager', 'HR Manager']);

        $this->widget([
            'widget_key' => 'employees_on_leave', 'name' => 'On Leave', 'type' => 'card', 'icon' => 'UserMinus',
            'permission' => 'attendance.view', 'order' => ++$order,
        ], $this->execGmHr());

        $this->widget([
            'widget_key' => 'active_sites', 'name' => 'Active Sites', 'type' => 'card', 'icon' => 'Building2',
            'permission' => 'site.view', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'General Manager', 'Production Manager', 'Workshop Supervisor', 'Design Manager']);

        $this->widget([
            'widget_key' => 'inventory_value', 'name' => 'Inventory Value', 'type' => 'card', 'icon' => 'Package',
            'permission' => 'inventory.view', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'Store Manager']);

        $this->widget([
            'widget_key' => 'low_stock', 'name' => 'Low Stock Items', 'type' => 'card', 'icon' => 'AlertTriangle',
            'permission' => 'inventory.view', 'order' => ++$order,
        ], ['Store Manager', 'Production Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'out_of_stock', 'name' => 'Out of Stock', 'type' => 'card', 'icon' => 'XCircle',
            'permission' => 'inventory.view', 'order' => ++$order,
        ], ['Store Manager', 'Production Manager']);

        $this->widget([
            'widget_key' => 'revenue', 'name' => 'Revenue', 'type' => 'card', 'icon' => 'TrendingUp',
            'permission' => 'invoice.view', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'General Manager', 'Finance Manager']);

        $this->widget([
            'widget_key' => 'expenses', 'name' => 'Expenses', 'type' => 'card', 'icon' => 'TrendingDown',
            'permission' => 'purchase-order.view', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'General Manager', 'Finance Manager', 'Accountant']);

        $this->widget([
            'widget_key' => 'payroll_cost', 'name' => 'Payroll Cost', 'type' => 'card', 'icon' => 'DollarSign',
            'permission' => 'payroll.view', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'Finance Manager', 'HR Manager']);

        $this->widget([
            'widget_key' => 'pending_approvals', 'name' => 'Pending Approvals', 'type' => 'card', 'icon' => 'ClipboardList',
            'permission' => null, 'order' => ++$order,
        ], ['Super Admin', 'Admin', 'General Manager', 'Production Manager', 'Finance Manager', 'HR Manager', 'Design Manager']);

        $this->widget([
            'widget_key' => 'new_joiners', 'name' => 'New Joiners', 'type' => 'card', 'icon' => 'UserPlus',
            'permission' => 'employee.view', 'order' => ++$order,
        ], ['HR Manager', 'Super Admin', 'Admin']);

        $this->widget([
            'widget_key' => 'pending_leave_requests', 'name' => 'Pending Leave Requests', 'type' => 'card', 'icon' => 'CalendarClock',
            'permission' => 'leave.view', 'order' => ++$order,
        ], ['HR Manager', 'Super Admin', 'Admin', 'General Manager', 'Production Manager']);

        $this->widget([
            'widget_key' => 'pending_dpr_approvals', 'name' => 'Pending DPR Approvals', 'type' => 'card', 'icon' => 'FileText',
            'permission' => 'daily-report.approve', 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'Super Admin', 'Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'document_expiry', 'name' => 'Document Expiry', 'type' => 'card', 'icon' => 'FileWarning',
            'permission' => null, 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'salary_processed', 'name' => 'Salary Processed', 'type' => 'card', 'icon' => 'Percent',
            'permission' => 'payroll.view', 'order' => ++$order,
        ], $this->financeExec());

        $this->widget([
            'widget_key' => 'pending_payroll', 'name' => 'Draft Payrolls', 'type' => 'card', 'icon' => 'Clock',
            'permission' => 'payroll.view', 'order' => ++$order,
        ], $this->financeExec());

        $this->widget([
            'widget_key' => 'approved_payroll', 'name' => 'Approved Payroll', 'type' => 'card', 'icon' => 'CheckCircle',
            'permission' => 'payroll.approve', 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'paid_payroll', 'name' => 'Paid Payroll', 'type' => 'card', 'icon' => 'CreditCard',
            'permission' => 'payroll.view', 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'payroll_records', 'name' => 'Payroll Records', 'type' => 'card', 'icon' => 'Database',
            'permission' => 'payroll.view', 'order' => ++$order,
        ], ['Finance Manager', 'Accountant', 'HR Manager']);

        $this->widget([
            'widget_key' => 'payslips', 'name' => 'Payslips', 'type' => 'card', 'icon' => 'FileText',
            'permission' => 'payslip.view', 'order' => ++$order,
        ], $this->finance());

        $this->widget([
            'widget_key' => 'pending_payments', 'name' => 'Pending Payments', 'type' => 'card', 'icon' => 'Wallet',
            'permission' => 'invoice.view', 'order' => ++$order,
        ], $this->finance());

        $this->widget([
            'widget_key' => 'production_workforce', 'name' => 'Production Workforce', 'type' => 'card', 'icon' => 'HardHat',
            'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'General Manager']);

        $this->widget([
            'widget_key' => 'site_productivity', 'name' => 'Site Productivity', 'type' => 'card', 'icon' => 'TrendingUp',
            'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'General Manager']);

        $this->widget([
            'widget_key' => 'assigned_employees', 'name' => 'Assigned Employees', 'type' => 'card', 'icon' => 'Users',
            'permission' => null, 'order' => ++$order,
        ], ['Workshop Supervisor', 'Production Manager']);

        $this->widget([
            'widget_key' => 'attendance_today', 'name' => 'Attendance Today', 'type' => 'card', 'icon' => 'ClipboardCheck',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'completed_work_reports', 'name' => 'Completed Reports', 'type' => 'card', 'icon' => 'CheckSquare',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'pending_work_reports', 'name' => 'Pending Reports', 'type' => 'card', 'icon' => 'Square',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'design_projects', 'name' => 'Design Projects', 'type' => 'card', 'icon' => 'Palette',
            'permission' => null, 'order' => ++$order,
        ], ['Design Manager', 'Designer']);

        $this->widget([
            'widget_key' => 'assigned_designers', 'name' => 'Design Team', 'type' => 'card', 'icon' => 'Users',
            'permission' => null, 'order' => ++$order,
        ], ['Design Manager']);

        $this->widget([
            'widget_key' => 'project_progress', 'name' => 'Project Progress', 'type' => 'card', 'icon' => 'BarChart3',
            'permission' => null, 'order' => ++$order,
        ], ['Design Manager', 'Designer']);

        $this->widget([
            'widget_key' => 'my_tasks', 'name' => "Today's Tasks", 'type' => 'card', 'icon' => 'ListChecks',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'my_projects', 'name' => 'My Sites', 'type' => 'card', 'icon' => 'Building2',
            'permission' => null, 'order' => ++$order,
        ], $this->fieldWorkers());

        $this->widget([
            'widget_key' => 'my_attendance', 'name' => 'My Attendance', 'type' => 'card', 'icon' => 'Calendar',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'my_dpr', 'name' => 'My DPRs', 'type' => 'card', 'icon' => 'FileText',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'leave_balance', 'name' => 'Leave Balance', 'type' => 'card', 'icon' => 'CalendarDays',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'today_attendance', 'name' => "Today's Attendance", 'type' => 'card', 'icon' => 'Fingerprint',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'current_site', 'name' => 'Current Site', 'type' => 'card', 'icon' => 'MapPin',
            'permission' => null, 'order' => ++$order,
        ], $this->fieldWorkers());

        $this->widget([
            'widget_key' => 'pending_leave_requests_mine', 'name' => 'My Leave Requests', 'type' => 'card', 'icon' => 'Clock',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'active_users', 'name' => 'Active Users', 'type' => 'card', 'icon' => 'Users',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'system_health', 'name' => 'System Health', 'type' => 'card', 'icon' => 'Activity',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'login_statistics', 'name' => 'Login Statistics', 'type' => 'card', 'icon' => 'LogIn',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'audit_logs', 'name' => 'Audit Logs', 'type' => 'card', 'icon' => 'ScrollText',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'failed_logins', 'name' => 'Failed Logins', 'type' => 'card', 'icon' => 'ShieldAlert',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'permission_changes', 'name' => 'Permission Changes', 'type' => 'card', 'icon' => 'Shield',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'server_status', 'name' => 'Server Status', 'type' => 'card', 'icon' => 'Server',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'recent_activity', 'name' => 'Recent Activity', 'type' => 'card', 'icon' => 'RefreshCw',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'employee_summary', 'name' => 'Employee Summary', 'type' => 'card', 'icon' => 'Users',
            'permission' => 'employee.view', 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'attendance_summary', 'name' => 'Attendance Summary', 'type' => 'card', 'icon' => 'ClipboardCheck',
            'permission' => 'attendance.view', 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'General Manager', 'Production Manager']);

        $this->widget([
            'widget_key' => 'leave_summary', 'name' => 'Leave Summary', 'type' => 'card', 'icon' => 'CalendarClock',
            'permission' => 'leave.view', 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'dpr_summary', 'name' => 'DPR Summary', 'type' => 'card', 'icon' => 'FileText',
            'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'Admin']);

        $this->widget([
            'widget_key' => 'payroll_summary', 'name' => 'Payroll Summary', 'type' => 'card', 'icon' => 'DollarSign',
            'permission' => 'payroll.view', 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'company_overview', 'name' => 'Company Overview', 'type' => 'card', 'icon' => 'Briefcase',
            'permission' => null, 'order' => ++$order,
        ], ['Super Admin', 'Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'site_performance', 'name' => 'Site Performance', 'type' => 'card', 'icon' => 'BarChart3',
            'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'General Manager']);

        $this->widget([
            'widget_key' => 'department_performance', 'name' => 'Department Performance', 'type' => 'card', 'icon' => 'Building',
            'permission' => null, 'order' => ++$order,
        ], ['Super Admin', 'Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'pending_transfers', 'name' => 'Pending Transfers', 'type' => 'card', 'icon' => 'ArrowLeftRight',
            'permission' => null, 'order' => ++$order,
        ], ['Store Manager', 'Admin']);

        $this->widget([
            'widget_key' => 'incoming_stock', 'name' => 'Incoming Stock', 'type' => 'card', 'icon' => 'Truck',
            'permission' => null, 'order' => ++$order,
        ], ['Store Manager', 'Production Manager']);

        $this->widget([
            'widget_key' => 'user_activity', 'name' => 'User Activity', 'type' => 'card', 'icon' => 'Activity',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin']);
    }

    // ─── CHARTS ────────────────────────────────────────────────────

    protected function seedCharts(): void
    {
        $order = 0;

        $this->widget([
            'widget_key' => 'attendance_trend', 'name' => 'Attendance Trend', 'type' => 'chart', 'icon' => 'BarChart3',
            'chart_type' => 'area', 'permission' => 'attendance.view', 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'Super Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'payroll_trend', 'name' => 'Payroll Trend', 'type' => 'chart', 'icon' => 'TrendingUp',
            'chart_type' => 'area', 'permission' => 'payroll.view', 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'inventory_trend', 'name' => 'Inventory Trend', 'type' => 'chart', 'icon' => 'Package',
            'chart_type' => 'area', 'permission' => 'inventory.view', 'order' => ++$order,
        ], ['Store Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'sales_trend', 'name' => 'Sales Trend', 'type' => 'chart', 'icon' => 'TrendingUp',
            'chart_type' => 'area', 'permission' => 'sales-order.view', 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'revenue_vs_expenses', 'name' => 'Revenue vs Expenses', 'type' => 'chart', 'icon' => 'BarChart3',
            'chart_type' => 'bar', 'permission' => null, 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'leave_trend', 'name' => 'Leave Trend', 'type' => 'chart', 'icon' => 'CalendarClock',
            'chart_type' => 'line', 'permission' => 'leave.view', 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'employee_growth', 'name' => 'Employee Growth', 'type' => 'chart', 'icon' => 'UserPlus',
            'chart_type' => 'area', 'permission' => 'employee.view', 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'Super Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'department_headcount', 'name' => 'Department Headcount', 'type' => 'chart', 'icon' => 'Building2',
            'chart_type' => 'bar', 'permission' => null, 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'Super Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'department_salary_cost', 'name' => 'Salary Cost by Dept', 'type' => 'chart', 'icon' => 'DollarSign',
            'chart_type' => 'bar', 'permission' => 'payroll.view', 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'expense_trend', 'name' => 'Expense Trend', 'type' => 'chart', 'icon' => 'TrendingDown',
            'chart_type' => 'area', 'permission' => null, 'order' => ++$order,
        ], $this->financeExec());

        $this->widget([
            'widget_key' => 'attendance_by_site', 'name' => 'Attendance by Site', 'type' => 'chart', 'icon' => 'Building2',
            'chart_type' => 'bar', 'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'Admin']);

        $this->widget([
            'widget_key' => 'dpr_trend', 'name' => 'DPR Trend', 'type' => 'chart', 'icon' => 'FileText',
            'chart_type' => 'line', 'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'Admin']);

        $this->widget([
            'widget_key' => 'workforce_utilization', 'name' => 'Workforce Utilization', 'type' => 'chart', 'icon' => 'PieChart',
            'chart_type' => 'pie', 'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'General Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'project_completion', 'name' => 'Project Completion', 'type' => 'chart', 'icon' => 'PieChart',
            'chart_type' => 'pie', 'permission' => null, 'order' => ++$order,
        ], ['Design Manager', 'Designer', 'Admin']);

        $this->widget([
            'widget_key' => 'designer_productivity', 'name' => 'Designer Productivity', 'type' => 'chart', 'icon' => 'BarChart3',
            'chart_type' => 'bar', 'permission' => null, 'order' => ++$order,
        ], ['Design Manager', 'Designer', 'Admin']);

        $this->widget([
            'widget_key' => 'inventory_movement', 'name' => 'Inventory Movement', 'type' => 'chart', 'icon' => 'ArrowLeftRight',
            'chart_type' => 'bar', 'permission' => null, 'order' => ++$order,
        ], ['Store Manager', 'Admin']);

        $this->widget([
            'widget_key' => 'warehouse_utilization', 'name' => 'Warehouse Utilization', 'type' => 'chart', 'icon' => 'PieChart',
            'chart_type' => 'pie', 'permission' => null, 'order' => ++$order,
        ], ['Store Manager', 'Admin']);

        $this->widget([
            'widget_key' => 'category_distribution', 'name' => 'Category Distribution', 'type' => 'chart', 'icon' => 'PieChart',
            'chart_type' => 'pie', 'permission' => null, 'order' => ++$order,
        ], ['Store Manager', 'Admin']);
    }

    // ─── QUICK ACTIONS ─────────────────────────────────────────────

    protected function seedQuickActions(): void
    {
        $order = 0;

        $this->widget([
            'widget_key' => 'add_employee', 'name' => 'Add Employee', 'type' => 'quick_action', 'icon' => 'UserPlus',
            'permission' => 'employee.create', 'order' => ++$order,
        ], ['Super Admin', 'System Admin', 'Admin', 'HR Manager']);

        $this->widget([
            'widget_key' => 'approve_leave', 'name' => 'Approve Leave', 'type' => 'quick_action', 'icon' => 'CheckCircle',
            'permission' => 'leave.approve', 'order' => ++$order,
        ], ['Super Admin', 'Admin', 'General Manager', 'HR Manager', 'Production Manager']);

        $this->widget([
            'widget_key' => 'generate_payroll', 'name' => 'Generate Payroll', 'type' => 'quick_action', 'icon' => 'DollarSign',
            'permission' => 'payroll.generate', 'order' => ++$order,
        ], ['Finance Manager', 'Accountant', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'create_purchase_order', 'name' => 'Create Purchase Order', 'type' => 'quick_action', 'icon' => 'ShoppingCart',
            'permission' => 'purchase-order.create', 'order' => ++$order,
        ], ['Store Manager', 'Admin', 'Production Manager']);

        $this->widget([
            'widget_key' => 'create_sales_order', 'name' => 'Create Sales Order', 'type' => 'quick_action', 'icon' => 'Receipt',
            'permission' => 'sales-order.create', 'order' => ++$order,
        ], ['Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'manage_users', 'name' => 'Manage Users', 'type' => 'quick_action', 'icon' => 'Users',
            'permission' => 'user.view', 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'manage_roles', 'name' => 'Manage Roles', 'type' => 'quick_action', 'icon' => 'Shield',
            'permission' => 'role.view', 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'manage_permissions', 'name' => 'Manage Permissions', 'type' => 'quick_action', 'icon' => 'Key',
            'permission' => 'permission.view', 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'system_settings', 'name' => 'System Settings', 'type' => 'quick_action', 'icon' => 'Settings',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'assign_site', 'name' => 'Assign Site', 'type' => 'quick_action', 'icon' => 'MapPin',
            'permission' => 'site.assign', 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'upload_documents', 'name' => 'Upload Documents', 'type' => 'quick_action', 'icon' => 'Upload',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'lock_payroll', 'name' => 'Lock Payroll', 'type' => 'quick_action', 'icon' => 'Lock',
            'permission' => 'payroll.lock', 'order' => ++$order,
        ], ['Finance Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'export_payslips', 'name' => 'Export Payslips', 'type' => 'quick_action', 'icon' => 'Download',
            'permission' => null, 'order' => ++$order,
        ], $this->finance());

        $this->widget([
            'widget_key' => 'generate_payslip', 'name' => 'Generate Payslip', 'type' => 'quick_action', 'icon' => 'FileText',
            'permission' => 'payslip.view', 'order' => ++$order,
        ], $this->finance());

        $this->widget([
            'widget_key' => 'export_payroll', 'name' => 'Export Payroll', 'type' => 'quick_action', 'icon' => 'DownloadCloud',
            'permission' => null, 'order' => ++$order,
        ], ['Finance Manager', 'Accountant']);

        $this->widget([
            'widget_key' => 'approve_dpr', 'name' => 'Approve DPR', 'type' => 'quick_action', 'icon' => 'ClipboardCheck',
            'permission' => 'daily-report.approve', 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'view_team_attendance', 'name' => 'View Team Attendance', 'type' => 'quick_action', 'icon' => 'Users',
            'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'HR Manager', 'General Manager']);

        $this->widget([
            'widget_key' => 'transfer_employee', 'name' => 'Transfer Employee', 'type' => 'quick_action', 'icon' => 'ArrowLeftRight',
            'permission' => 'site.transfer', 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'review_attendance', 'name' => 'Review Attendance', 'type' => 'quick_action', 'icon' => 'ClipboardList',
            'permission' => null, 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'submit_dpr', 'name' => 'Submit DPR', 'type' => 'quick_action', 'icon' => 'FilePlus',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'apply_leave', 'name' => 'Apply Leave', 'type' => 'quick_action', 'icon' => 'CalendarPlus',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'check_attendance', 'name' => 'Check Attendance', 'type' => 'quick_action', 'icon' => 'Fingerprint',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'add_product', 'name' => 'Add Product', 'type' => 'quick_action', 'icon' => 'PackagePlus',
            'permission' => 'product.create', 'order' => ++$order,
        ], ['Admin', 'Store Manager']);

        $this->widget([
            'widget_key' => 'transfer_stock', 'name' => 'Transfer Stock', 'type' => 'quick_action', 'icon' => 'ArrowLeftRight',
            'permission' => null, 'order' => ++$order,
        ], ['Store Manager', 'Admin']);

        $this->widget([
            'widget_key' => 'stock_adjustment', 'name' => 'Stock Adjustment', 'type' => 'quick_action', 'icon' => 'ClipboardList',
            'permission' => null, 'order' => ++$order,
        ], ['Store Manager', 'Admin']);

        $this->widget([
            'widget_key' => 'manage_access', 'name' => 'Manage Access', 'type' => 'quick_action', 'icon' => 'Shield',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'view_logs', 'name' => 'View Logs', 'type' => 'quick_action', 'icon' => 'ScrollText',
            'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'check_in', 'name' => 'Check In', 'type' => 'quick_action', 'icon' => 'LogIn',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'check_out', 'name' => 'Check Out', 'type' => 'quick_action', 'icon' => 'LogOut',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'download_payslip', 'name' => 'Download Payslip', 'type' => 'quick_action', 'icon' => 'Download',
            'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());
    }

    // ─── ALERTS ────────────────────────────────────────────────────

    protected function seedAlerts(): void
    {
        $order = 0;

        $this->widget([
            'widget_key' => 'low_stock_alert', 'name' => 'Low Stock Alert', 'type' => 'alert', 'icon' => 'AlertTriangle',
            'config' => ['severity' => 'warning'], 'permission' => 'inventory.view', 'order' => ++$order,
        ], ['Store Manager', 'Production Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'document_expiry_alert', 'name' => 'Document Expiry Alert', 'type' => 'alert', 'icon' => 'FileWarning',
            'config' => ['severity' => 'warning'], 'permission' => null, 'order' => ++$order,
        ], ['HR Manager', 'Admin', 'Super Admin']);

        $this->widget([
            'widget_key' => 'pending_payroll_alert', 'name' => 'Pending Payroll Alert', 'type' => 'alert', 'icon' => 'Clock',
            'config' => ['severity' => 'info'], 'permission' => 'payroll.view', 'order' => ++$order,
        ], $this->financeExec());

        $this->widget([
            'widget_key' => 'absenteeism_alert', 'name' => 'High Absenteeism Alert', 'type' => 'alert', 'icon' => 'UserX',
            'config' => ['severity' => 'warning'], 'permission' => 'attendance.view', 'order' => ++$order,
        ], ['HR Manager', 'Production Manager', 'Admin', 'General Manager']);

        $this->widget([
            'widget_key' => 'attendance_reminder', 'name' => 'Attendance Reminder', 'type' => 'alert', 'icon' => 'Bell',
            'config' => ['severity' => 'info'], 'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'dpr_reminder', 'name' => 'DPR Submission Reminder', 'type' => 'alert', 'icon' => 'Bell',
            'config' => ['severity' => 'info'], 'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'leave_expiry_alert', 'name' => 'Leave Balance Expiry', 'type' => 'alert', 'icon' => 'CalendarClock',
            'config' => ['severity' => 'info'], 'permission' => null, 'order' => ++$order,
        ], $this->allWorkers());

        $this->widget([
            'widget_key' => 'site_delays_alert', 'name' => 'Site Delays', 'type' => 'alert', 'icon' => 'Clock',
            'config' => ['severity' => 'warning'], 'permission' => null, 'order' => ++$order,
        ], ['Production Manager', 'Workshop Supervisor', 'General Manager']);

        $this->widget([
            'widget_key' => 'server_alert', 'name' => 'Server Alert', 'type' => 'alert', 'icon' => 'AlertCircle',
            'config' => ['severity' => 'critical'], 'permission' => null, 'order' => ++$order,
        ], ['IT Manager', 'System Admin', 'Super Admin']);
    }
}
