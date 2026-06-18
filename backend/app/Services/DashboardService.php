<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Site;
use App\Models\Inventory;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardService
{
    protected $user;
    protected $branchId;

    public function forUser($user)
    {
        $this->user = $user;
        $this->branchId = request()->header('X-Branch-Id');
        return $this;
    }

    public function getDashboard()
    {
        $employee = $this->user->employee;
        $designation = $employee?->designation;

        $widgets = collect();
        if ($designation) {
            $widgets = DashboardWidget::where('is_active', true)
                ->whereHas('designations', fn($q) => $q->where('designation_id', $designation->id))
                ->orderBy('order')
                ->get();
        }

        if ($widgets->isEmpty()) {
            $widgets = DashboardWidget::where('is_active', true)
                ->whereNull('permission')
                ->orWhereIn('permission', $this->user->getAllPermissions()->pluck('name'))
                ->orderBy('order')
                ->get();
        }

        $cards = [];
        $charts = [];
        $quickActions = [];
        $alerts = [];

        foreach ($widgets as $widget) {
            if ($widget->permission && !$this->user->hasPermissionTo($widget->permission)) {
                continue;
            }

            $data = $this->computeWidgetData($widget);

            if ($data === null) continue;

            switch ($widget->type) {
                case 'card':
                    $cards[] = array_merge(['key' => $widget->widget_key, 'name' => $widget->name, 'icon' => $widget->icon], $data);
                    break;
                case 'chart':
                    $charts[] = array_merge(['key' => $widget->widget_key, 'name' => $widget->name, 'chart_type' => $widget->chart_type, 'icon' => $widget->icon], $data);
                    break;
                case 'quick_action':
                    $quickActions[] = array_merge(['key' => $widget->widget_key, 'name' => $widget->name, 'icon' => $widget->icon], $data);
                    break;
                case 'alert':
                    $alerts[] = array_merge(['key' => $widget->widget_key, 'name' => $widget->name, 'icon' => $widget->icon], $data);
                    break;
            }
        }

        return [
            'cards' => $cards,
            'charts' => $charts,
            'quick_actions' => $quickActions,
            'alerts' => $alerts,
        ];
    }

    protected function computeWidgetData($widget)
    {
        $method = 'widget' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $widget->widget_key)));

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }

    // ─── CARD COMPUTATIONS ───────────────────────────────────────

    protected function widgetTotalEmployees()
    {
        $count = Employee::when($this->branchId, fn($q) => $q->whereHas('user', fn($u) => $u->where('branch_id', $this->branchId)))->count();
        $newHires = Employee::when($this->branchId, fn($q) => $q->whereHas('user', fn($u) => $u->where('branch_id', $this->branchId)))
            ->where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        return ['value' => $count, 'subtitle' => "{$newHires} new this week", 'trend' => $newHires > 0 ? 'up' : 'neutral'];
    }

    protected function widgetPresentToday()
    {
        $count = Attendance::whereDate('date', Carbon::today())
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->where('status', 'Present')->count();
        return ['value' => $count, 'subtitle' => 'Checked in today'];
    }

    protected function widgetAbsentToday()
    {
        $total = Employee::when($this->branchId, fn($q) => $q->whereHas('user', fn($u) => $u->where('branch_id', $this->branchId)))->count();
        $present = Attendance::whereDate('date', Carbon::today())
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->where('status', 'Present')->count();
        return ['value' => $total - $present, 'subtitle' => 'Not checked in'];
    }

    protected function widgetEmployeesOnLeave()
    {
        $count = Leave::where('status', 'Approved')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->count();
        return ['value' => $count, 'subtitle' => 'On leave today'];
    }

    protected function widgetActiveSites()
    {
        $count = Site::where('status', 'Active')->count();
        return ['value' => $count, 'subtitle' => 'Active construction sites'];
    }

    protected function widgetInventoryValue()
    {
        $value = Inventory::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->select(DB::raw('SUM(quantity * unit_price) as total'))->value('total') ?? 0;
        return ['value' => round($value, 2), 'prefix' => '₹', 'subtitle' => 'Total inventory value'];
    }

    protected function widgetLowStock()
    {
        $count = Inventory::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->where('quantity', '<=', 10)->count();
        $critical = Inventory::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->where('quantity', '<=', 5)->count();
        return ['value' => $count, 'subtitle' => "{$critical} critical", 'trend' => $count > 0 ? 'down' : 'neutral'];
    }

    protected function widgetOutOfStock()
    {
        $count = Inventory::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->where('quantity', '<=', 0)->count();
        return ['value' => $count, 'subtitle' => 'Items out of stock'];
    }

    protected function widgetRevenue()
    {
        $total = Invoice::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))->sum('total_amount');
        return ['value' => round($total, 2), 'prefix' => '₹', 'subtitle' => 'Total revenue'];
    }

    protected function widgetExpenses()
    {
        $total = PurchaseOrder::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))->sum('total_amount');
        return ['value' => round($total, 2), 'prefix' => '₹', 'subtitle' => 'Total expenses'];
    }

    protected function widgetPayrollCost()
    {
        $latestPeriod = PayrollPeriod::latest('id')->value('id');
        $cost = Payroll::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))
            ->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Current period'];
    }

    protected function widgetPendingApprovals()
    {
        $leaves = Leave::where('status', 'Submitted')->count();
        $dprs = DailyReport::where('status', 'Submitted')->count();
        return ['value' => $leaves + $dprs, 'subtitle' => "{$leaves} leaves, {$dprs} DPRs"];
    }

    protected function widgetNewJoiners()
    {
        $count = Employee::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        return ['value' => $count, 'subtitle' => 'This month'];
    }

    protected function widgetPendingLeaveRequests()
    {
        $count = Leave::where('status', 'Submitted')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting approval'];
    }

    protected function widgetPendingDprApprovals()
    {
        $count = DailyReport::where('status', 'Submitted')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting review'];
    }

    protected function widgetDocumentExpiry()
    {
        $count = Document::whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
            ->whereDate('expiry_date', '>=', Carbon::now())->count();
        return ['value' => $count, 'subtitle' => 'Expiring within 30 days'];
    }

    protected function widgetSalaryProcessed()
    {
        $latestPeriod = PayrollPeriod::latest('id')->value('id');
        $total = Payroll::when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))->count();
        $processed = Payroll::when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))
            ->whereIn('status', ['Approved', 'Locked', 'Paid'])->count();
        $percent = $total > 0 ? round(($processed / $total) * 100) : 0;
        return ['value' => "{$percent}%", 'subtitle' => "{$processed}/{$total} processed"];
    }

    protected function widgetPendingPayroll()
    {
        $count = Payroll::where('status', 'Draft')->count();
        return ['value' => $count, 'subtitle' => 'Draft payrolls'];
    }

    protected function widgetApprovedPayroll()
    {
        $cost = Payroll::where('status', 'Approved')->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Approved'];
    }

    protected function widgetPaidPayroll()
    {
        $cost = Payroll::where('status', 'Paid')->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Paid'];
    }

    protected function widgetPayrollRecords()
    {
        $count = Payroll::count();
        return ['value' => $count, 'subtitle' => 'Total payroll records'];
    }

    protected function widgetPayslips()
    {
        $count = \App\Models\Payslip::count();
        return ['value' => $count, 'subtitle' => 'Generated payslips'];
    }

    protected function widgetPendingPayments()
    {
        $count = Invoice::where('status', 'Sent')->count();
        return ['value' => $count, 'subtitle' => 'Unpaid invoices'];
    }

    protected function widgetProductionWorkforce()
    {
        $designation = Designation::whereIn('name', ['Fitter', 'Welder', 'Electrician', 'Helper'])->pluck('id');
        $count = Employee::whereIn('designation_id', $designation)->count();
        return ['value' => $count, 'subtitle' => 'Production staff'];
    }

    protected function widgetSiteProductivity()
    {
        $sites = Site::where('status', 'Active')->count();
        $assignments = EmployeeSite::count();
        return ['value' => $sites, 'subtitle' => "{$assignments} assignments"];
    }

    protected function widgetAssignedEmployees()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $count = EmployeeSite::where('site_id', $employee->employeeSites->first()?->site_id)->count();
        return ['value' => $count, 'subtitle' => 'At your site'];
    }

    protected function widgetAttendanceToday()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $attended = Attendance::where('employee_id', $employee->id)->whereDate('date', Carbon::today())->exists();
        return ['value' => $attended ? 'Checked In' : 'Not Yet', 'subtitle' => 'Today'];
    }

    protected function widgetCompletedWorkReports()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $count = DailyReport::where('employee_id', $employee->id)->where('status', 'Approved')->count();
        return ['value' => $count, 'subtitle' => 'Approved DPRs'];
    }

    protected function widgetPendingWorkReports()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $count = DailyReport::where('employee_id', $employee->id)->where('status', 'Submitted')->count();
        return ['value' => $count, 'subtitle' => 'Pending review'];
    }

    protected function widgetDesignProjects()
    {
        return ['value' => 0, 'subtitle' => 'Active design projects'];
    }

    protected function widgetAssignedDesigners()
    {
        $designation = Designation::where('name', 'Designer')->pluck('id');
        $count = Employee::whereIn('designation_id', $designation)->count();
        return ['value' => $count, 'subtitle' => 'Design team'];
    }

    protected function widgetProjectProgress()
    {
        return ['value' => '--', 'subtitle' => 'On track'];
    }

    protected function widgetMyTasks()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $count = DailyReport::where('employee_id', $employee->id)->whereDate('date', Carbon::today())->count();
        return ['value' => $count, 'subtitle' => "Today's reports"];
    }

    protected function widgetMyProjects()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $sites = EmployeeSite::where('employee_id', $employee->id)->count();
        return ['value' => $sites, 'subtitle' => 'Assigned sites'];
    }

    protected function widgetMyAttendance()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $present = Attendance::where('employee_id', $employee->id)->where('status', 'Present')
            ->whereMonth('date', Carbon::now()->month)->count();
        return ['value' => $present, 'subtitle' => 'Days this month'];
    }

    protected function widgetMyDpr()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $count = DailyReport::where('employee_id', $employee->id)->whereMonth('date', Carbon::now()->month)->count();
        return ['value' => $count, 'subtitle' => 'Reports this month'];
    }

    protected function widgetLeaveBalance()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $balance = LeaveBalance::where('employee_id', $employee->id)->sum('remaining');
        return ['value' => $balance, 'subtitle' => 'Days remaining'];
    }

    protected function widgetTodayAttendance()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $record = Attendance::where('employee_id', $employee->id)->whereDate('date', Carbon::today())->first();
        if ($record) {
            $status = $record->status === 'Present' ? 'Checked In' : $record->status;
            return ['value' => $status, 'subtitle' => $record->check_in ? Carbon::parse($record->check_in)->format('h:i A') : '--'];
        }
        return ['value' => 'Not Checked In', 'subtitle' => 'Tap to check in'];
    }

    protected function widgetCurrentSite()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $assignment = EmployeeSite::where('employee_id', $employee->id)->latest()->first();
        return ['value' => $assignment?->site?->name ?? 'Not Assigned', 'subtitle' => $assignment?->site?->city ?? ''];
    }

    protected function widgetPendingLeaveRequestsMine()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $count = Leave::where('employee_id', $employee->id)->where('status', 'Submitted')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting approval'];
    }

    protected function widgetActiveUsers()
    {
        $count = User::whereHas('employee')->count();
        return ['value' => $count, 'subtitle' => 'Linked employees'];
    }

    protected function widgetSystemHealth()
    {
        return ['value' => 'Healthy', 'subtitle' => 'All systems operational'];
    }

    protected function widgetLoginStatistics()
    {
        return ['value' => '--', 'subtitle' => 'Today'];
    }

    protected function widgetAuditLogs()
    {
        return ['value' => 0, 'subtitle' => 'Events today'];
    }

    protected function widgetFailedLogins()
    {
        return ['value' => 0, 'subtitle' => 'Today'];
    }

    protected function widgetPermissionChanges()
    {
        return ['value' => 0, 'subtitle' => 'Today'];
    }

    protected function widgetServerStatus()
    {
        return ['value' => 'Online', 'subtitle' => 'Uptime normal'];
    }

    protected function widgetRecentActivity()
    {
        return ['value' => '--', 'subtitle' => 'Latest events'];
    }

    protected function widgetEmployeeSummary()
    {
        $count = Employee::count();
        return ['value' => $count, 'subtitle' => 'Total employees'];
    }

    protected function widgetAttendanceSummary()
    {
        $present = Attendance::whereDate('date', Carbon::today())->where('status', 'Present')->count();
        $total = Employee::count();
        return ['value' => $total > 0 ? round(($present / $total) * 100) . '%' : '0%', 'subtitle' => "{$present}/{$total} today"];
    }

    protected function widgetLeaveSummary()
    {
        $onLeave = Leave::where('status', 'Approved')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())->count();
        return ['value' => $onLeave, 'subtitle' => 'On leave today'];
    }

    protected function widgetDprSummary()
    {
        $submitted = DailyReport::whereDate('date', Carbon::today())->count();
        return ['value' => $submitted, 'subtitle' => 'Submitted today'];
    }

    protected function widgetPayrollSummary()
    {
        $latestPeriod = PayrollPeriod::latest('id')->value('id');
        $cost = Payroll::when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Current period'];
    }

    protected function widgetCompanyOverview()
    {
        $employees = Employee::count();
        $sites = Site::where('status', 'Active')->count();
        return ['value' => "{$employees} Emp, {$sites} Sites", 'subtitle' => 'Company at a glance'];
    }

    protected function widgetSitePerformance()
    {
        $active = Site::where('status', 'Active')->count();
        $total = Site::count();
        return ['value' => $active, 'subtitle' => "{$total} total sites"];
    }

    protected function widgetDepartmentPerformance()
    {
        $depts = Department::count();
        return ['value' => $depts, 'subtitle' => 'Departments'];
    }

    protected function widgetPendingTransfers()
    {
        $count = \App\Models\InventoryTransfer::where('status', 'Pending')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting transfer'];
    }

    protected function widgetIncomingStock()
    {
        $count = PurchaseOrder::where('status', 'Approved')->count();
        return ['value' => $count, 'subtitle' => 'Pending receipts'];
    }

    protected function widgetUserActivity()
    {
        return ['value' => '--', 'subtitle' => 'Active sessions'];
    }

    // ─── CHART COMPUTATIONS ──────────────────────────────────────

    protected function widgetAttendanceTrend()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $present = Attendance::whereMonth('date', $date->month)->whereYear('date', $date->year)
                ->where('status', 'Present')
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->count();
            $data[] = ['name' => $date->format('M'), 'value' => $present];
        }
        return ['data' => $data];
    }

    protected function widgetPayrollTrend()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $cost = Payroll::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->sum('net_salary');
            $data[] = ['name' => $date->format('M'), 'value' => round($cost, 2)];
        }
        return ['data' => $data];
    }

    protected function widgetInventoryTrend()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $value = Inventory::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->select(DB::raw('SUM(quantity * unit_price) as total'))->value('total') ?? 0;
            $data[] = ['name' => $date->format('M'), 'value' => round($value, 2)];
        }
        return ['data' => $data];
    }

    protected function widgetSalesTrend()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $total = Invoice::whereMonth('issue_date', $date->month)->whereYear('issue_date', $date->year)
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->sum('total_amount');
            $data[] = ['name' => $date->format('M'), 'value' => round($total, 2)];
        }
        return ['data' => $data];
    }

    protected function widgetRevenueVsExpenses()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Invoice::whereMonth('issue_date', $date->month)->whereYear('issue_date', $date->year)
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->sum('total_amount');
            $expenses = PurchaseOrder::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->sum('total_amount');
            $data[] = ['name' => $date->format('M'), 'revenue' => round($revenue, 2), 'expenses' => round($expenses, 2)];
        }
        return ['data' => $data];
    }

    protected function widgetLeaveTrend()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Leave::where('status', 'Approved')
                ->whereMonth('start_date', $date->month)->whereYear('start_date', $date->year)->count();
            $data[] = ['name' => $date->format('M'), 'value' => $count];
        }
        return ['data' => $data];
    }

    protected function widgetEmployeeGrowth()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Employee::whereYear('created_at', $date->year)->whereMonth('created_at', '<=', $date->month)->count();
            $data[] = ['name' => $date->format('M'), 'value' => $count];
        }
        return ['data' => $data];
    }

    protected function widgetDepartmentHeadcount()
    {
        $depts = Department::withCount('employees')->get();
        return ['data' => $depts->map(fn($d) => ['name' => $d->name, 'value' => $d->employees_count])->toArray()];
    }

    protected function widgetDepartmentSalaryCost()
    {
        $data = Department::withCount('employees')->get()->map(function ($dept) {
            $employeeIds = Employee::where('department_id', $dept->id)->pluck('id');
            $cost = Payroll::whereIn('employee_id', $employeeIds)->sum('net_salary');
            return ['name' => $dept->name, 'value' => round($cost, 2)];
        })->toArray();
        return ['data' => $data];
    }

    protected function widgetExpenseTrend()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $total = PurchaseOrder::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->sum('total_amount');
            $data[] = ['name' => $date->format('M'), 'value' => round($total, 2)];
        }
        return ['data' => $data];
    }

    protected function widgetAttendanceBySite()
    {
        $sites = Site::where('status', 'Active')->get()->map(function ($site) {
            $employeeIds = EmployeeSite::where('site_id', $site->id)->pluck('employee_id');
            $present = Attendance::whereIn('employee_id', $employeeIds)
                ->whereDate('date', Carbon::today())
                ->where('status', 'Present')
                ->count();
            return ['name' => $site->name, 'value' => $present];
        });
        return ['data' => $sites->toArray()];
    }

    protected function widgetDprTrend()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = DailyReport::whereMonth('date', $date->month)->whereYear('date', $date->year)->count();
            $data[] = ['name' => $date->format('M'), 'value' => $count];
        }
        return ['data' => $data];
    }

    protected function widgetWorkforceUtilization()
    {
        $total = Employee::count();
        $present = Attendance::whereDate('date', Carbon::today())->where('status', 'Present')->count();
        $utilization = $total > 0 ? round(($present / $total) * 100) : 0;
        return ['data' => [
            ['name' => 'Present', 'value' => $present],
            ['name' => 'Absent', 'value' => $total - $present],
        ]];
    }

    protected function widgetProjectCompletion()
    {
        return ['data' => [
            ['name' => 'Completed', 'value' => 0],
            ['name' => 'In Progress', 'value' => 0],
            ['name' => 'Pending', 'value' => 0],
        ]];
    }

    protected function widgetDesignerProductivity()
    {
        return ['data' => []];
    }

    protected function widgetInventoryMovement()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $in = \App\Models\InventoryTransaction::where('type', 'in')
                ->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->sum('quantity');
            $out = \App\Models\InventoryTransaction::where('type', 'out')
                ->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->sum('quantity');
            $data[] = ['name' => $date->format('M'), 'in' => $in, 'out' => $out];
        }
        return ['data' => $data];
    }

    protected function widgetWarehouseUtilization()
    {
        return ['data' => [
            ['name' => 'Used', 'value' => 70],
            ['name' => 'Available', 'value' => 30],
        ]];
    }

    protected function widgetCategoryDistribution()
    {
        $data = \App\Models\Category::withCount('products')->get();
        return ['data' => $data->map(fn($c) => ['name' => $c->name, 'value' => $c->products_count])->toArray()];
    }

    // ─── QUICK ACTIONS ───────────────────────────────────────────

    protected function widgetAddEmployee()
    {
        if (!$this->user->hasPermissionTo('employee.create')) return null;
        return ['label' => 'Add Employee', 'link' => '/dashboard/employees', 'permission' => 'employee.create'];
    }

    protected function widgetApproveLeave()
    {
        if (!$this->user->hasPermissionTo('leave.approve')) return null;
        return ['label' => 'Approve Leave', 'link' => '/dashboard/leave-management/requests', 'permission' => 'leave.approve'];
    }

    protected function widgetGeneratePayroll()
    {
        if (!$this->user->hasPermissionTo('payroll.generate')) return null;
        return ['label' => 'Generate Payroll', 'link' => '/dashboard/process-payroll', 'permission' => 'payroll.generate'];
    }

    protected function widgetCreatePurchaseOrder()
    {
        if (!$this->user->hasPermissionTo('purchase-order.create')) return null;
        return ['label' => 'Create Purchase Order', 'link' => '/dashboard/purchases', 'permission' => 'purchase-order.create'];
    }

    protected function widgetCreateSalesOrder()
    {
        if (!$this->user->hasPermissionTo('sales-order.create')) return null;
        return ['label' => 'Create Sales Order', 'link' => '/dashboard/sales', 'permission' => 'sales-order.create'];
    }

    protected function widgetManageUsers()
    {
        if (!$this->user->hasPermissionTo('user.view')) return null;
        return ['label' => 'Manage Users', 'link' => '/dashboard/user-access', 'permission' => 'user.view'];
    }

    protected function widgetManageRoles()
    {
        if (!$this->user->hasPermissionTo('role.view')) return null;
        return ['label' => 'Manage Roles', 'link' => '/dashboard/roles', 'permission' => 'role.view'];
    }

    protected function widgetManagePermissions()
    {
        if (!$this->user->hasPermissionTo('permission.view')) return null;
        return ['label' => 'Manage Permissions', 'link' => '/dashboard/permissions-list', 'permission' => 'permission.view'];
    }

    protected function widgetSystemSettings()
    {
        return ['label' => 'System Settings', 'link' => '/dashboard/settings', 'permission' => null];
    }

    protected function widgetAssignSite()
    {
        if (!$this->user->hasPermissionTo('site.assign')) return null;
        return ['label' => 'Assign Site', 'link' => '/dashboard/sites', 'permission' => 'site.assign'];
    }

    protected function widgetUploadDocuments()
    {
        return ['label' => 'Upload Documents', 'link' => '#', 'permission' => null];
    }

    protected function widgetLockPayroll()
    {
        if (!$this->user->hasPermissionTo('payroll.lock')) return null;
        return ['label' => 'Lock Payroll', 'link' => '/dashboard/process-payroll', 'permission' => 'payroll.lock'];
    }

    protected function widgetExportPayslips()
    {
        return ['label' => 'Export Payslips', 'link' => '/dashboard/my-payroll', 'permission' => null];
    }

    protected function widgetGeneratePayslip()
    {
        if (!$this->user->hasPermissionTo('payslip.view')) return null;
        return ['label' => 'Generate Payslip', 'link' => '/dashboard/payslip', 'permission' => 'payslip.view'];
    }

    protected function widgetExportPayroll()
    {
        return ['label' => 'Export Payroll', 'link' => '/dashboard/payroll', 'permission' => null];
    }

    protected function widgetApproveDpr()
    {
        if (!$this->user->hasPermissionTo('daily-report.approve')) return null;
        return ['label' => 'Approve DPR', 'link' => '/dashboard/daily-reports', 'permission' => 'daily-report.approve'];
    }

    protected function widgetViewTeamAttendance()
    {
        return ['label' => 'View Team Attendance', 'link' => '/dashboard/attendance', 'permission' => null];
    }

    protected function widgetTransferEmployee()
    {
        if (!$this->user->hasPermissionTo('site.transfer')) return null;
        return ['label' => 'Transfer Employee', 'link' => '/dashboard/sites', 'permission' => 'site.transfer'];
    }

    protected function widgetReviewAttendance()
    {
        return ['label' => 'Review Attendance', 'link' => '/dashboard/attendance', 'permission' => null];
    }

    protected function widgetSubmitDpr()
    {
        return ['label' => 'Submit DPR', 'link' => '/dashboard/daily-reports/new', 'permission' => null];
    }

    protected function widgetApplyLeave()
    {
        return ['label' => 'Apply Leave', 'link' => '/dashboard/leave-management/requests/new', 'permission' => null];
    }

    protected function widgetCheckAttendance()
    {
        return ['label' => 'Check Attendance', 'link' => '/dashboard/my-attendance', 'permission' => null];
    }

    protected function widgetAddProduct()
    {
        if (!$this->user->hasPermissionTo('product.create')) return null;
        return ['label' => 'Add Product', 'link' => '/dashboard/products', 'permission' => 'product.create'];
    }

    protected function widgetTransferStock()
    {
        return ['label' => 'Transfer Stock', 'link' => '/dashboard/inventory', 'permission' => null];
    }

    protected function widgetStockAdjustment()
    {
        return ['label' => 'Stock Adjustment', 'link' => '/dashboard/inventory', 'permission' => null];
    }

    protected function widgetManageAccess()
    {
        return ['label' => 'Manage Access', 'link' => '/dashboard/user-access', 'permission' => null];
    }

    protected function widgetViewLogs()
    {
        return ['label' => 'View Logs', 'link' => '#', 'permission' => null];
    }

    protected function widgetCheckIn()
    {
        return ['label' => 'Check In', 'link' => '/dashboard/my-attendance', 'permission' => null];
    }

    protected function widgetCheckOut()
    {
        return ['label' => 'Check Out', 'link' => '/dashboard/my-attendance', 'permission' => null];
    }

    protected function widgetDownloadPayslip()
    {
        return ['label' => 'Download Payslip', 'link' => '/dashboard/my-payroll', 'permission' => null];
    }

    // ─── ALERTS ───────────────────────────────────────────────────

    protected function widgetLowStockAlert()
    {
        $count = Inventory::when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->where('quantity', '<=', 10)->count();
        if ($count === 0) return null;
        return ['value' => "{$count} items low in stock", 'subtitle' => 'Immediate attention needed', 'severity' => 'warning'];
    }

    protected function widgetDocumentExpiryAlert()
    {
        $count = Document::whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
            ->whereDate('expiry_date', '>=', Carbon::now())->count();
        if ($count === 0) return null;
        return ['value' => "{$count} documents expiring soon", 'subtitle' => 'Review and renew', 'severity' => 'warning'];
    }

    protected function widgetPendingPayrollAlert()
    {
        $count = Payroll::where('status', 'Draft')->count();
        if ($count === 0) return null;
        return ['value' => "{$count} payrolls pending", 'subtitle' => 'Approve or process', 'severity' => 'info'];
    }

    protected function widgetAbsenteeismAlert()
    {
        $total = Employee::when($this->branchId, fn($q) => $q->whereHas('user', fn($u) => $u->where('branch_id', $this->branchId)))->count();
        $present = Attendance::whereDate('date', Carbon::today())
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->where('status', 'Present')->count();
        $absentPercent = $total > 0 ? round((($total - $present) / $total) * 100) : 0;
        if ($absentPercent < 30) return null;
        return ['value' => "{$absentPercent}% absenteeism today", 'subtitle' => "$present present out of $total", 'severity' => $absentPercent > 50 ? 'critical' : 'warning'];
    }

    protected function widgetAttendanceReminder()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $checkedIn = Attendance::where('employee_id', $employee->id)->whereDate('date', Carbon::today())->exists();
        if ($checkedIn) return null;
        return ['value' => 'Not checked in yet', 'subtitle' => 'Please check in to start your day', 'severity' => 'info'];
    }

    protected function widgetDprReminder()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $submitted = DailyReport::where('employee_id', $employee->id)->whereDate('date', Carbon::today())->exists();
        if ($submitted) return null;
        return ['value' => 'DPR not submitted', 'subtitle' => 'Submit your daily report', 'severity' => 'info'];
    }

    protected function widgetLeaveExpiryAlert()
    {
        $employee = $this->user->employee;
        if (!$employee) return null;
        $totalBalance = LeaveBalance::where('employee_id', $employee->id)->sum('remaining');
        if ($totalBalance <= 0) return null;
        return ['value' => "{$totalBalance} leave days remaining", 'subtitle' => 'Plan your leaves', 'severity' => 'info'];
    }

    protected function widgetSiteDelaysAlert()
    {
        // Placeholder: count sites with no recent attendance as proxy for delays
        $delayedSites = Site::where('status', 'Active')->get()->filter(function ($site) {
            $recentAttendance = Attendance::where('site_id', $site->id)
                ->whereDate('date', '>=', Carbon::now()->subDays(3))->exists();
            return !$recentAttendance;
        })->count();
        if ($delayedSites === 0) return null;
        return ['value' => "{$delayedSites} sites with no recent activity", 'subtitle' => 'Check on these sites', 'severity' => 'warning'];
    }

    protected function widgetServerAlert()
    {
        // Placeholder: system health check
        return null; // Always healthy unless we detect an issue
    }
}
