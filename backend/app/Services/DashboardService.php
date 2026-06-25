<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Site;
use App\Models\ProductStock;
use App\Models\TransactionLedger;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Department;
use App\Models\Designation;
use App\Models\DailyReport;
use App\Models\Document;
use App\Models\SalesOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\EmployeeSite;
use App\Models\DashboardWidget;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    protected $user;
    protected $employee;

    private const ROLE_MAP = [
        'Admin' => 'admin',
        'Manager' => 'admin',
        'HR' => 'hr',
        'Designer' => 'employee',
        'Accountant' => 'finance',
        'Developer' => 'it',
        'Fitter' => 'employee',
        'Welder' => 'employee',
        'Electrician' => 'employee',
        'Helper' => 'employee',
        'Store Manager' => 'inventory',
    ];

    private const DASHBOARD_WIDGETS = [
        'admin' => [
            'cards' => ['total_employees', 'present_today', 'employees_on_leave', 'active_sites', 'inventory_value', 'low_stock', 'revenue', 'expenses', 'payroll_cost', 'pending_approvals'],
            'charts' => ['attendance_trend', 'payroll_trend', 'inventory_trend', 'sales_trend', 'department_headcount'],
            'quick_actions' => ['add_employee', 'generate_payroll', 'approve_leave', 'create_purchase_order', 'create_sales_order'],
            'alerts' => ['low_stock_alert', 'pending_payroll_alert', 'absenteeism_alert', 'document_expiry_alert'],
        ],
        'executive' => [
            'cards' => ['total_employees', 'present_today', 'employees_on_leave', 'revenue', 'payroll_cost', 'pending_approvals'],
            'charts' => ['attendance_trend', 'revenue_vs_expenses', 'department_headcount'],
            'quick_actions' => ['add_employee', 'approve_leave', 'approve_dpr'],
            'alerts' => ['absenteeism_alert', 'pending_payroll_alert'],
        ],
        'hr' => [
            'cards' => ['total_employees', 'present_today', 'absent_today', 'employees_on_leave', 'new_joiners', 'pending_leave_requests', 'pending_dpr_approvals', 'document_expiry'],
            'charts' => ['attendance_trend', 'leave_trend', 'employee_growth', 'department_headcount'],
            'quick_actions' => ['add_employee', 'assign_site', 'approve_leave', 'upload_documents'],
            'alerts' => ['document_expiry_alert', 'absenteeism_alert'],
        ],
        'finance' => [
            'cards' => ['payroll_cost', 'salary_processed', 'pending_payroll', 'approved_payroll', 'paid_payroll', 'outstanding_payments'],
            'charts' => ['payroll_trend', 'department_salary_cost', 'expense_trend'],
            'quick_actions' => ['generate_payroll', 'lock_payroll', 'export_payslips'],
            'alerts' => ['pending_payroll_alert'],
        ],
        'inventory' => [
            'cards' => ['inventory_value', 'low_stock', 'out_of_stock', 'pending_transfers', 'incoming_stock'],
            'charts' => ['inventory_movement', 'warehouse_utilization', 'category_distribution'],
            'quick_actions' => ['add_product', 'transfer_stock', 'stock_adjustment'],
            'alerts' => ['low_stock_alert'],
        ],
        'production' => [
            'cards' => ['present_today', 'absent_today', 'production_workforce', 'pending_dpr_approvals', 'completed_work_reports'],
            'charts' => ['attendance_by_site', 'dpr_trend', 'workforce_utilization'],
            'quick_actions' => ['approve_dpr', 'view_team_attendance', 'transfer_employee'],
            'alerts' => ['absenteeism_alert', 'dpr_reminder'],
        ],
        'sales' => [
            'cards' => ['revenue', 'pending_payments', 'customers_count', 'sales_trend'],
            'charts' => ['sales_trend', 'revenue_vs_expenses'],
            'quick_actions' => ['create_sales_order', 'create_purchase_order'],
            'alerts' => [],
        ],
        'it' => [
            'cards' => ['active_users', 'system_health'],
            'charts' => ['attendance_trend'],
            'quick_actions' => ['manage_users', 'manage_roles', 'view_logs'],
            'alerts' => [],
        ],
        'employee' => [
            'cards' => ['attendance_today', 'current_site', 'leave_balance', 'pending_leave_requests_mine'],
            'charts' => [],
            'quick_actions' => ['check_attendance', 'submit_dpr', 'apply_leave', 'download_payslip'],
            'alerts' => ['attendance_reminder', 'dpr_reminder', 'leave_expiry_alert'],
            'widgets' => ['my_attendance', 'my_dpr', 'my_documents', 'my_payslips'],
        ],
    ];

    public function forUser($user): static
    {
        $this->user = $user;
        $this->employee = $user->employee;
        return $this;
    }

    public function getDashboardType(): string
    {
        $roleNames = $this->user->roles->pluck('name')->toArray();
        foreach ($roleNames as $role) {
            if (isset(self::ROLE_MAP[$role])) {
                return self::ROLE_MAP[$role];
            }
        }
        return 'employee';
    }

    public function getDashboard(): array
    {
        $type = $this->getDashboardType();
        $config = self::DASHBOARD_WIDGETS[$type] ?? self::DASHBOARD_WIDGETS['employee'];

        $allKeys = array_merge(
            $config['cards'] ?? [],
            $config['charts'] ?? [],
            $config['quick_actions'] ?? [],
            $config['alerts'] ?? [],
            $config['widgets'] ?? [],
        );

        $widgets = DashboardWidget::where('is_active', true)
            ->whereIn('widget_key', $allKeys)
            ->with('designations')
            ->orderBy('order')
            ->get()
            ->filter(function ($widget) {
                if ($widget->designations->isEmpty()) return true;
                $userDesignationIds = $this->user->roles
                    ->pluck('name')
                    ->pipe(fn($names) => Designation::whereIn('name', $names)->pluck('id'));
                return $widget->designations->pluck('id')->intersect($userDesignationIds)->isNotEmpty();
            })
            ->keyBy('widget_key');

        $cards = [];
        $charts = [];
        $quickActions = [];
        $alerts = [];
        $widgetsOutput = [];

        foreach ($config['cards'] ?? [] as $key) {
            $widget = $widgets->get($key);
            if (!$widget) continue;
            $data = $this->computeWidgetData($widget);
            if ($data === null) continue;
            $cards[] = array_merge([
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'icon' => $widget->icon,
            ], $data);
        }

        foreach ($config['charts'] ?? [] as $key) {
            $widget = $widgets->get($key);
            if (!$widget) continue;
            $data = $this->computeWidgetData($widget);
            if ($data === null) continue;
            $charts[] = array_merge([
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'icon' => $widget->icon,
                'chart_type' => $widget->chart_type,
            ], $data);
        }

        foreach ($config['quick_actions'] ?? [] as $key) {
            $widget = $widgets->get($key);
            if (!$widget) continue;
            $data = $this->computeWidgetData($widget);
            if ($data === null) continue;
            $quickActions[] = array_merge([
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'icon' => $widget->icon,
                'label' => $data['label'] ?? $widget->name,
                'link' => $data['link'] ?? '#',
                'permission' => $data['permission'] ?? null,
            ], $data);
        }

        foreach ($config['alerts'] ?? [] as $key) {
            $widget = $widgets->get($key);
            if (!$widget) continue;
            $data = $this->computeWidgetData($widget);
            if ($data === null) continue;
            $alerts[] = array_merge([
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'icon' => $widget->icon,
                'value' => $data['value'] ?? '',
                'subtitle' => $data['subtitle'] ?? null,
                'severity' => $data['severity'] ?? 'info',
            ], $data);
        }

        foreach ($config['widgets'] ?? [] as $key) {
            $widget = $widgets->get($key);
            if (!$widget) continue;
            $data = $this->computeWidgetData($widget);
            if ($data === null) continue;
            $widgetsOutput[] = array_merge([
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'icon' => $widget->icon,
            ], $data);
        }

        return [
            'dashboard_type' => $type,
            'cards' => $cards,
            'charts' => $charts,
            'quick_actions' => $quickActions,
            'alerts' => $alerts,
            'widgets' => $widgetsOutput,
        ];
    }

    protected function computeWidgetData($widget): ?array
    {
        $method = 'widget' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $widget->widget_key)));
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return null;
    }

    // ─── CARD COMPUTATIONS ───────────────────────────────────────

    protected function widgetTotalEmployees(): array
    {
        $count = Employee::count();
        $newHires = Employee::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        return ['value' => $count, 'subtitle' => "{$newHires} new this week", 'trend' => $newHires > 0 ? 'up' : 'neutral'];
    }

    protected function widgetPresentToday(): array
    {
        $count = Attendance::whereDate('date', Carbon::today())
            ->where('status', 'Present')->count();
        return ['value' => $count, 'subtitle' => 'Checked in today'];
    }

    protected function widgetAbsentToday(): array
    {
        $total = Employee::count();
        $present = Attendance::whereDate('date', Carbon::today())
            ->where('status', 'Present')->count();
        return ['value' => $total - $present, 'subtitle' => 'Not checked in'];
    }

    protected function widgetEmployeesOnLeave(): array
    {
        $count = Leave::where('status', 'Approved')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())->count();
        return ['value' => $count, 'subtitle' => 'On leave today'];
    }

    protected function widgetActiveSites(): array
    {
        $count = Site::where('status', 'Active')->count();
        return ['value' => $count, 'subtitle' => 'Active construction sites'];
    }

    protected function widgetInventoryValue(): array
    {
        $value = ProductStock::join('products', 'product_stock.product_id', '=', 'products.id')
            ->sum(DB::raw('product_stock.quantity * products.cost_price'));
        return ['value' => round($value, 2), 'prefix' => '₹', 'subtitle' => 'Total inventory value'];
    }

    protected function widgetLowStock(): array
    {
        $count = ProductStock::whereHas('product', fn($q) => $q->whereColumn('product_stock.quantity', '<=', 'products.reorder_level'))->count();
        return ['value' => $count, 'subtitle' => 'Items below reorder level', 'trend' => $count > 0 ? 'down' : 'neutral'];
    }

    protected function widgetOutOfStock(): array
    {
        $count = ProductStock::where('quantity', '<=', 0)->count();
        return ['value' => $count, 'subtitle' => 'Items out of stock'];
    }

    protected function widgetRevenue(): array
    {
        $total = Invoice::sum('total_amount');
        return ['value' => round($total, 2), 'prefix' => '₹', 'subtitle' => 'Total revenue'];
    }

    protected function widgetExpenses(): array
    {
        $total = PurchaseOrder::sum('total_amount');
        return ['value' => round($total, 2), 'prefix' => '₹', 'subtitle' => 'Total expenses'];
    }

    protected function widgetPayrollCost(): array
    {
        $latestPeriod = PayrollPeriod::latest('id')->value('id');
        $cost = Payroll::when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))
            ->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Current period'];
    }

    protected function widgetPendingApprovals(): array
    {
        $leaves = Leave::where('status', 'Submitted')->count();
        $dprs = DailyReport::where('status', 'Submitted')->count();
        return ['value' => $leaves + $dprs, 'subtitle' => "{$leaves} leaves, {$dprs} DPRs"];
    }

    protected function widgetNewJoiners(): array
    {
        $count = Employee::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        return ['value' => $count, 'subtitle' => 'This month'];
    }

    protected function widgetPendingLeaveRequests(): array
    {
        $count = Leave::where('status', 'Submitted')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting approval'];
    }

    protected function widgetPendingDprApprovals(): array
    {
        $count = DailyReport::where('status', 'Submitted')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting review'];
    }

    protected function widgetDocumentExpiry(): array
    {
        $count = Document::whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
            ->whereDate('expiry_date', '>=', Carbon::now())->count();
        return ['value' => $count, 'subtitle' => 'Expiring within 30 days'];
    }

    protected function widgetSalaryProcessed(): array
    {
        $latestPeriod = PayrollPeriod::latest('id')->value('id');
        $total = Payroll::when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))->count();
        $processed = Payroll::when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))
            ->whereIn('status', ['Approved', 'Locked', 'Paid'])->count();
        $percent = $total > 0 ? round(($processed / $total) * 100) : 0;
        return ['value' => "{$percent}%", 'subtitle' => "{$processed}/{$total} processed"];
    }

    protected function widgetPendingPayroll(): array
    {
        $count = Payroll::where('status', 'Draft')->count();
        return ['value' => $count, 'subtitle' => 'Draft payrolls'];
    }

    protected function widgetApprovedPayroll(): array
    {
        $cost = Payroll::where('status', 'Approved')->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Approved'];
    }

    protected function widgetPaidPayroll(): array
    {
        $cost = Payroll::where('status', 'Paid')->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Paid'];
    }

    protected function widgetOutstandingPayments(): array
    {
        $count = Invoice::where('status', 'Sent')->count();
        return ['value' => $count, 'subtitle' => 'Unpaid invoices'];
    }

    protected function widgetProductionWorkforce(): array
    {
        $designation = Designation::whereIn('name', ['Fitter', 'Welder', 'Electrician', 'Helper'])->pluck('id');
        $count = Employee::whereIn('designation_id', $designation)->count();
        return ['value' => $count, 'subtitle' => 'Production staff'];
    }

    protected function widgetCompletedWorkReports(): array
    {
        if (!$this->employee) return null;
        $count = DailyReport::where('employee_id', $this->employee->id)->where('status', 'Approved')->count();
        return ['value' => $count, 'subtitle' => 'Approved DPRs'];
    }

    protected function widgetAttendanceToday(): array
    {
        if (!$this->employee) return null;
        $attended = Attendance::where('employee_id', $this->employee->id)->whereDate('date', Carbon::today())->exists();
        return ['value' => $attended ? 'Checked In' : 'Not Yet', 'subtitle' => 'Today'];
    }

    protected function widgetCurrentSite(): array
    {
        if (!$this->employee) return null;
        $assignment = EmployeeSite::where('employee_id', $this->employee->id)->latest()->first();
        return ['value' => $assignment?->site?->name ?? 'Not Assigned', 'subtitle' => $assignment?->site?->city ?? ''];
    }

    protected function widgetLeaveBalance(): array
    {
        if (!$this->employee) return null;
        $balance = LeaveBalance::where('employee_id', $this->employee->id)->sum('remaining');
        return ['value' => $balance, 'subtitle' => 'Days remaining'];
    }

    protected function widgetPendingLeaveRequestsMine(): array
    {
        if (!$this->employee) return null;
        $count = Leave::where('employee_id', $this->employee->id)->where('status', 'Submitted')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting approval'];
    }

    protected function widgetActiveUsers(): array
    {
        $count = User::whereHas('employee')->count();
        return ['value' => $count, 'subtitle' => 'Linked employees'];
    }

    protected function widgetSystemHealth(): array
    {
        return ['value' => 'Healthy', 'subtitle' => 'All systems operational'];
    }

    protected function widgetPendingTransfers(): array
    {
        $count = \App\Models\InventoryTransfer::where('status', 'Pending')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting transfer'];
    }

    protected function widgetIncomingStock(): array
    {
        $count = PurchaseOrder::where('status', 'Approved')->count();
        return ['value' => $count, 'subtitle' => 'Pending receipts'];
    }

    protected function widgetCustomersCount(): array
    {
        $count = \App\Models\Customer::count();
        return ['value' => $count, 'subtitle' => 'Total customers'];
    }

    protected function widgetSalesTrend(): array
    {
        $total = Invoice::sum('total_amount');
        return ['value' => round($total, 2), 'prefix' => '₹', 'subtitle' => 'Total sales'];
    }

    // ─── CHART COMPUTATIONS ──────────────────────────────────────

    protected function widgetAttendanceTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $present = Attendance::whereMonth('date', $date->month)->whereYear('date', $date->year)
                ->where('status', 'Present')
                ->count();
            $data[] = ['name' => $date->format('M'), 'value' => $present];
        }
        return ['data' => $data, 'chart_type' => 'area'];
    }

    protected function widgetPayrollTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $cost = Payroll::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->sum('net_salary');
            $data[] = ['name' => $date->format('M'), 'value' => round($cost, 2)];
        }
        return ['data' => $data, 'chart_type' => 'bar'];
    }

    protected function widgetInventoryTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $value = ProductStock::join('products', 'product_stock.product_id', '=', 'products.id')
                ->select(DB::raw('SUM(product_stock.quantity * products.cost_price) as total'))
                ->value('total') ?? 0;
            $data[] = ['name' => $date->format('M'), 'value' => round($value, 2)];
        }
        return ['data' => $data, 'chart_type' => 'area'];
    }

    protected function widgetSalesTrendChart(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $total = Invoice::whereMonth('issue_date', $date->month)->whereYear('issue_date', $date->year)
                ->sum('total_amount');
            $data[] = ['name' => $date->format('M'), 'value' => round($total, 2)];
        }
        return ['data' => $data, 'chart_type' => 'area'];
    }

    protected function widgetRevenueVsExpenses(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Invoice::whereMonth('issue_date', $date->month)->whereYear('issue_date', $date->year)
                ->sum('total_amount');
            $expenses = PurchaseOrder::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->sum('total_amount');
            $data[] = ['name' => $date->format('M'), 'revenue' => round($revenue, 2), 'expenses' => round($expenses, 2)];
        }
        return ['data' => $data, 'chart_type' => 'bar'];
    }

    protected function widgetLeaveTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Leave::where('status', 'Approved')
                ->whereMonth('start_date', $date->month)->whereYear('start_date', $date->year)->count();
            $data[] = ['name' => $date->format('M'), 'value' => $count];
        }
        return ['data' => $data, 'chart_type' => 'line'];
    }

    protected function widgetEmployeeGrowth(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Employee::whereYear('created_at', $date->year)->whereMonth('created_at', '<=', $date->month)->count();
            $data[] = ['name' => $date->format('M'), 'value' => $count];
        }
        return ['data' => $data, 'chart_type' => 'area'];
    }

    protected function widgetDepartmentHeadcount(): array
    {
        $depts = Department::withCount('employees')->get();
        return ['data' => $depts->map(fn($d) => ['name' => $d->name, 'value' => $d->employees_count])->toArray(), 'chart_type' => 'bar'];
    }

    protected function widgetDepartmentSalaryCost(): array
    {
        $data = Department::withCount('employees')->get()->map(function ($dept) {
            $employeeIds = Employee::where('department_id', $dept->id)->pluck('id');
            $cost = Payroll::whereIn('employee_id', $employeeIds)->sum('net_salary');
            return ['name' => $dept->name, 'value' => round($cost, 2)];
        })->toArray();
        return ['data' => $data, 'chart_type' => 'bar'];
    }

    protected function widgetExpenseTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $total = PurchaseOrder::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->sum('total_amount');
            $data[] = ['name' => $date->format('M'), 'value' => round($total, 2)];
        }
        return ['data' => $data, 'chart_type' => 'area'];
    }

    protected function widgetAttendanceBySite(): array
    {
        $sites = Site::where('status', 'Active')->get()->map(function ($site) {
            $employeeIds = EmployeeSite::where('site_id', $site->id)->pluck('employee_id');
            $present = Attendance::whereIn('employee_id', $employeeIds)
                ->whereDate('date', Carbon::today())
                ->where('status', 'Present')
                ->count();
            return ['name' => $site->name, 'value' => $present];
        });
        return ['data' => $sites->toArray(), 'chart_type' => 'bar'];
    }

    protected function widgetDprTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = DailyReport::whereMonth('date', $date->month)->whereYear('date', $date->year)->count();
            $data[] = ['name' => $date->format('M'), 'value' => $count];
        }
        return ['data' => $data, 'chart_type' => 'bar'];
    }

    protected function widgetWorkforceUtilization(): array
    {
        $total = Employee::count();
        $present = Attendance::whereDate('date', Carbon::today())->where('status', 'Present')->count();
        return ['data' => [
            ['name' => 'Present', 'value' => $present],
            ['name' => 'Absent', 'value' => $total - $present],
        ], 'chart_type' => 'pie'];
    }

    protected function widgetInventoryMovement(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $in = TransactionLedger::whereIn('transaction_type', ['purchase', 'purchase_return', 'transfer_in', 'opening_stock', 'sales_return'])
                ->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->sum('quantity');
            $out = TransactionLedger::whereIn('transaction_type', ['sales', 'transfer_out', 'damage', 'adjustment', 'purchase_return'])
                ->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->sum('quantity');
            $data[] = ['name' => $date->format('M'), 'in' => $in, 'out' => $out];
        }
        return ['data' => $data, 'chart_type' => 'bar'];
    }

    protected function widgetWarehouseUtilization(): array
    {
        $total = ProductStock::sum('quantity');
        $reserved = ProductStock::sum('reserved_quantity');
        $available = $total - $reserved;
        return ['data' => [
            ['name' => 'Available', 'value' => max(0, $available)],
            ['name' => 'Reserved', 'value' => $reserved],
        ], 'chart_type' => 'pie'];
    }

    protected function widgetCategoryDistribution(): array
    {
        $data = Category::withCount('products')->get();
        return ['data' => $data->map(fn($c) => ['name' => $c->name, 'value' => $c->products_count])->toArray(), 'chart_type' => 'pie'];
    }

    // ─── QUICK ACTIONS ───────────────────────────────────────────

    protected function widgetAddEmployee(): array
    {
        return ['label' => 'Add Employee', 'link' => '/dashboard/employees'];
    }

    protected function widgetApproveLeave(): array
    {
        return ['label' => 'Approve Leave', 'link' => '/dashboard/leave-management/requests'];
    }

    protected function widgetGeneratePayroll(): array
    {
        return ['label' => 'Generate Payroll', 'link' => '/dashboard/process-payroll'];
    }

    protected function widgetCreatePurchaseOrder(): array
    {
        return ['label' => 'Create Purchase Order', 'link' => '/dashboard/purchases'];
    }

    protected function widgetCreateSalesOrder(): array
    {
        return ['label' => 'Create Sales Order', 'link' => '/dashboard/sales'];
    }

    protected function widgetAssignSite(): array
    {
        return ['label' => 'Assign Site', 'link' => '/dashboard/sites'];
    }

    protected function widgetUploadDocuments(): array
    {
        return ['label' => 'Upload Documents', 'link' => '/dashboard/employees'];
    }

    protected function widgetLockPayroll(): array
    {
        return ['label' => 'Lock Payroll', 'link' => '/dashboard/process-payroll'];
    }

    protected function widgetExportPayslips(): array
    {
        return ['label' => 'Export Payslips', 'link' => '/dashboard/my-payroll'];
    }

    protected function widgetAddProduct(): array
    {
        return ['label' => 'Add Product', 'link' => '/dashboard/products'];
    }

    protected function widgetTransferStock(): array
    {
        return ['label' => 'Transfer Stock', 'link' => '/dashboard/inventory/transfers'];
    }

    protected function widgetStockAdjustment(): array
    {
        return ['label' => 'Stock Adjustment', 'link' => '/dashboard/inventory/transactions'];
    }

    protected function widgetApproveDpr(): array
    {
        return ['label' => 'Approve DPR', 'link' => '/dashboard/daily-reports'];
    }

    protected function widgetViewTeamAttendance(): array
    {
        return ['label' => 'View Attendance', 'link' => '/dashboard/attendance'];
    }

    protected function widgetTransferEmployee(): array
    {
        return ['label' => 'Assign Workforce', 'link' => '/dashboard/sites'];
    }

    protected function widgetCheckAttendance(): array
    {
        return ['label' => 'Mark Attendance', 'link' => '/dashboard/my-attendance'];
    }

    protected function widgetSubmitDpr(): array
    {
        return ['label' => 'Submit DPR', 'link' => '/dashboard/daily-reports/new'];
    }

    protected function widgetApplyLeave(): array
    {
        return ['label' => 'Apply Leave', 'link' => '/dashboard/leave-management/requests/new'];
    }

    protected function widgetDownloadPayslip(): array
    {
        return ['label' => 'Download Payslip', 'link' => '/dashboard/my-payroll'];
    }

    protected function widgetManageUsers(): array
    {
        return ['label' => 'Manage Users', 'link' => '/dashboard/user-access'];
    }

    protected function widgetManageRoles(): array
    {
        return ['label' => 'Manage Roles', 'link' => '/dashboard/roles'];
    }

    protected function widgetViewLogs(): array
    {
        return ['label' => 'View Logs', 'link' => '/dashboard'];
    }

    // ─── ALERTS ───────────────────────────────────────────────────

    protected function widgetLowStockAlert(): ?array
    {
        $count = ProductStock::whereHas('product', fn($q) => $q->whereColumn('product_stock.quantity', '<=', 'products.reorder_level'))->count();
        if ($count === 0) return null;
        return ['value' => "{$count} items low in stock", 'subtitle' => 'Immediate attention needed', 'severity' => 'warning'];
    }

    protected function widgetDocumentExpiryAlert(): ?array
    {
        $count = Document::whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
            ->whereDate('expiry_date', '>=', Carbon::now())->count();
        if ($count === 0) return null;
        return ['value' => "{$count} documents expiring soon", 'subtitle' => 'Review and renew', 'severity' => 'warning'];
    }

    protected function widgetPendingPayrollAlert(): ?array
    {
        $count = Payroll::where('status', 'Draft')->count();
        if ($count === 0) return null;
        return ['value' => "{$count} payrolls pending", 'subtitle' => 'Approve or process', 'severity' => 'info'];
    }

    protected function widgetAbsenteeismAlert(): ?array
    {
        $total = Employee::count();
        $present = Attendance::whereDate('date', Carbon::today())
            ->where('status', 'Present')->count();
        $absentPercent = $total > 0 ? round((($total - $present) / $total) * 100) : 0;
        if ($absentPercent < 30) return null;
        return ['value' => "{$absentPercent}% absenteeism today", 'subtitle' => "$present present out of $total", 'severity' => $absentPercent > 50 ? 'critical' : 'warning'];
    }

    protected function widgetAttendanceReminder(): ?array
    {
        if (!$this->employee) return null;
        $checkedIn = Attendance::where('employee_id', $this->employee->id)->whereDate('date', Carbon::today())->exists();
        if ($checkedIn) return null;
        return ['value' => 'Not checked in yet', 'subtitle' => 'Please check in to start your day', 'severity' => 'info'];
    }

    protected function widgetDprReminder(): ?array
    {
        if (!$this->employee) return null;
        $submitted = DailyReport::where('employee_id', $this->employee->id)->whereDate('date', Carbon::today())->exists();
        if ($submitted) return null;
        return ['value' => 'DPR not submitted', 'subtitle' => 'Submit your daily report', 'severity' => 'info'];
    }

    protected function widgetLeaveExpiryAlert(): ?array
    {
        if (!$this->employee) return null;
        $totalBalance = LeaveBalance::where('employee_id', $this->employee->id)->sum('remaining');
        if ($totalBalance <= 0) return null;
        return ['value' => "{$totalBalance} leave days remaining", 'subtitle' => 'Plan your leaves', 'severity' => 'info'];
    }

    // ─── EMPLOYEE WIDGETS ────────────────────────────────────────

    protected function widgetMyAttendance(): ?array
    {
        if (!$this->employee) return null;
        $present = Attendance::where('employee_id', $this->employee->id)->where('status', 'Present')
            ->whereMonth('date', Carbon::now()->month)->count();
        return ['type' => 'mini_card', 'value' => $present, 'subtitle' => 'Days this month'];
    }

    protected function widgetMyDpr(): ?array
    {
        if (!$this->employee) return null;
        $count = DailyReport::where('employee_id', $this->employee->id)->whereMonth('date', Carbon::now()->month)->count();
        return ['type' => 'mini_card', 'value' => $count, 'subtitle' => 'Reports this month'];
    }

    protected function widgetMyDocuments(): ?array
    {
        if (!$this->employee) return null;
        $count = Document::where('employee_id', $this->employee->id)->count();
        return ['type' => 'mini_card', 'value' => $count, 'subtitle' => 'Documents on file'];
    }

    protected function widgetMyPayslips(): ?array
    {
        if (!$this->employee) return null;
        $count = \App\Models\Payslip::whereHas('payroll', fn($q) => $q->where('employee_id', $this->employee->id))->count();
        return ['type' => 'mini_card', 'value' => $count, 'subtitle' => 'Payslips available'];
    }
}
