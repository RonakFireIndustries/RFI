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
use App\Models\Payslip;
use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\CompanySetting;
use App\Support\Access;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardService
{
    protected $user;
    protected $employee;

    private const ROLE_MAP = [
        'Admin'                => 'admin',
        'System Admin'         => 'admin',
        'Manager'              => 'admin',
        'General Manager'      => 'executive',
        'HR'                   => 'hr',
        'HR Manager'           => 'hr',
        'Accountant'           => 'finance',
        'Store Manager'        => 'inventory',
        'IT Manager'           => 'it',
        'Developer'            => 'it',
        'Production Manager'   => 'production',
        'Workshop Supervisor'  => 'production',
        'Sales'                => 'sales',
        'Design Manager'       => 'employee',
        'Designer'             => 'employee',
        'Employee'             => 'employee',
        'Fitter'               => 'employee',
        'Welder'               => 'employee',
        'Electrician'          => 'employee',
        'Helper'               => 'employee',
    ];

    private const DASHBOARD_WIDGETS = [
        'admin' => [
            'cards' => ['total_employees', 'present_today', 'employees_on_leave', 'active_sites', 'inventory_value', 'low_stock', 'revenue', 'expenses', 'payroll_cost', 'pending_approvals', 'employee_summary', 'attendance_summary', 'dpr_summary', 'recent_activity'],
            'charts' => ['attendance_trend', 'payroll_trend', 'inventory_trend', 'sales_trend', 'department_headcount', 'department_performance', 'site_performance'],
            'quick_actions' => ['add_employee', 'generate_payroll', 'approve_leave', 'create_purchase_order', 'create_sales_order'],
            'alerts' => ['low_stock_alert', 'pending_payroll_alert', 'absenteeism_alert', 'document_expiry_alert', 'site_delays_alert'],
        ],
        'executive' => [
            'cards' => ['company_overview', 'total_employees', 'present_today', 'employees_on_leave', 'revenue', 'payroll_cost', 'pending_approvals', 'employee_summary'],
            'charts' => ['attendance_trend', 'revenue_vs_expenses', 'department_headcount', 'department_performance', 'site_performance'],
            'quick_actions' => ['add_employee', 'approve_leave', 'approve_dpr'],
            'alerts' => ['absenteeism_alert', 'pending_payroll_alert'],
        ],
        'hr' => [
            'cards' => ['total_employees', 'present_today', 'absent_today', 'employees_on_leave', 'new_joiners', 'pending_leave_requests', 'pending_dpr_approvals', 'document_expiry', 'attendance_summary', 'leave_summary'],
            'charts' => ['attendance_trend', 'leave_trend', 'employee_growth', 'department_headcount'],
            'quick_actions' => ['add_employee', 'assign_site', 'approve_leave', 'upload_documents', 'review_attendance'],
            'alerts' => ['document_expiry_alert', 'absenteeism_alert'],
        ],
        'finance' => [
            'cards' => ['payroll_cost', 'salary_processed', 'pending_payroll', 'approved_payroll', 'paid_payroll', 'outstanding_payments', 'payroll_records', 'payslips', 'payroll_summary', 'pending_payments'],
            'charts' => ['payroll_trend', 'department_salary_cost', 'expense_trend'],
            'quick_actions' => ['generate_payroll', 'lock_payroll', 'export_payslips', 'generate_payslip', 'export_payroll'],
            'alerts' => ['pending_payroll_alert'],
        ],
        'inventory' => [
            'cards' => ['inventory_value', 'low_stock', 'out_of_stock', 'pending_transfers', 'incoming_stock'],
            'charts' => ['inventory_movement', 'warehouse_utilization', 'category_distribution'],
            'quick_actions' => ['add_product', 'transfer_stock', 'stock_adjustment'],
            'alerts' => ['low_stock_alert'],
        ],
        'production' => [
            'cards' => ['present_today', 'absent_today', 'production_workforce', 'pending_dpr_approvals', 'completed_work_reports', 'site_productivity', 'assigned_employees', 'pending_work_reports', 'dpr_summary'],
            'charts' => ['attendance_by_site', 'dpr_trend', 'workforce_utilization'],
            'quick_actions' => ['approve_dpr', 'view_team_attendance', 'transfer_employee'],
            'alerts' => ['absenteeism_alert', 'dpr_reminder', 'site_delays_alert'],
        ],
        'sales' => [
            'cards' => ['revenue', 'pending_payments', 'customers_count', 'sales_trend'],
            'charts' => ['sales_trend', 'revenue_vs_expenses'],
            'quick_actions' => ['create_sales_order', 'create_purchase_order'],
            'alerts' => [],
        ],
        'it' => [
            'cards' => ['active_users', 'system_health', 'audit_logs', 'server_status', 'user_activity'],
            'charts' => ['attendance_trend'],
            'quick_actions' => ['manage_users', 'manage_roles', 'view_logs', 'manage_access', 'manage_permissions', 'system_settings'],
            'alerts' => ['server_alert'],
        ],
        'employee' => [
            'cards' => ['attendance_today', 'current_site', 'leave_balance', 'pending_leave_requests_mine', 'today_attendance', 'my_tasks'],
            'charts' => [],
            'quick_actions' => ['check_attendance', 'submit_dpr', 'apply_leave', 'download_payslip', 'check_in', 'check_out'],
            'alerts' => ['attendance_reminder', 'dpr_reminder', 'leave_expiry_alert'],
            'widgets' => ['my_attendance', 'my_dpr', 'my_documents', 'my_payslips'],
        ],
    ];

    /**
     * Ordered keyword -> lucide-icon rules used to pick an icon for a
     * synthesized widget, so a dashboard rendered before the seeder has run
     * still looks like the seeded one. Keyword matching (rather than a
     * key => icon table) keeps every widget covered automatically as widgets
     * are added.
     */
    private const ICON_RULES = [
        [['trend', 'growth', 'movement', 'utilization', 'performance', 'distribution', 'productivity', 'summary', 'overview', 'comparison'], 'BarChart3'],
        [['alert', 'low', 'expiry', 'reminder', 'delay'], 'AlertTriangle'],
        [['audit', 'log', 'activity', 'history'], 'ScrollText'],
        [['permission', 'access', 'role'], 'ShieldCheck'],
        [['setting', 'system', 'server', 'health'], 'Settings'],
        [['lock'], 'LockKeyhole'],
        [['export', 'download'], 'Download'],
        [['import', 'upload'], 'Upload'],
        [['payslip'], 'CreditCard'],
        [['payroll', 'salary', 'pay'], 'Wallet'],
        [['expense', 'payment'], 'Receipt'],
        [['revenue', 'income'], 'TrendingUp'],
        [['sales', 'order', 'customer'], 'ShoppingCart'],
        [['stock', 'inventory', 'warehouse', 'material', 'category', 'product'], 'Package'],
        [['site'], 'MapPin'],
        [['employee', 'staff', 'user', 'workforce', 'headcount', 'people', 'manager', 'joiner'], 'Users'],
        [['leave'], 'CalendarDays'],
        [['attendance', 'absentee', 'check_in', 'check_out', 'present', 'absent'], 'CalendarCheck'],
        [['dpr', 'report', 'document', 'file', 'task'], 'FileText'],
        [['approval', 'approve', 'review', 'pending'], 'ClipboardCheck'],
        [['transfer'], 'ArrowLeftRight'],
        [['add', 'create', 'generate', 'submit', 'apply', 'assign', 'manage', 'check'], 'PlusCircle'],
    ];

    /**
     * Resolve a lucide icon name from a widget key. The first matching keyword
     * rule wins; anything unrecognised falls back to a neutral chart icon,
     * which the frontend also uses for missing icons.
     *
     * Keywords match on word prefix so plural and inflected forms resolve to
     * the same icon (employee/employees, payslip/payslips).
     */
    private static function fallbackIcon(string $key): string
    {
        $words = preg_split('/[_\-\s]+/', strtolower($key));

        foreach (self::ICON_RULES as [$keywords, $icon]) {
            foreach ($keywords as $keyword) {
                foreach ($words as $word) {
                    if ($word !== '' && str_starts_with($word, $keyword)) {
                        return $icon;
                    }
                }
            }
        }

        return 'BarChart3';
    }

    /**
     * The seeded roles that belong to each dashboard type. Widget role lists are
     * derived from this (see rolesForWidgetKey) so they cannot drift from the
     * widget configuration above.
     */
    private const TYPE_ROLES = [
        'admin'      => ['Admin', 'Manager', 'System Admin'],
        'executive'  => ['General Manager'],
        'hr'         => ['HR', 'HR Manager'],
        'finance'    => ['Accountant'],
        'inventory'  => ['Store Manager'],
        'production' => ['Workshop Supervisor', 'Production Manager'],
        'sales'      => ['Sales'],
        'it'         => ['IT Manager', 'Developer'],
        'employee'   => ['Employee', 'Designer', 'Design Manager', 'Fitter', 'Welder', 'Electrician', 'Helper'],
    ];

    /**
     * Every dashboard type that renders the given widget key, with the roles
     * those types belong to. Returns an empty array for unknown keys.
     */
    public static function rolesForWidgetKey(string $widgetKey): array
    {
        $roles = [];

        foreach (self::DASHBOARD_WIDGETS as $type => $config) {
            $keys = array_merge(
                $config['cards'] ?? [],
                $config['charts'] ?? [],
                $config['quick_actions'] ?? [],
                $config['alerts'] ?? [],
                $config['widgets'] ?? [],
            );

            if (in_array($widgetKey, $keys, true)) {
                foreach (self::TYPE_ROLES[$type] ?? [] as $role) {
                    $roles[$role] = true;
                }
            }
        }

        return array_keys($roles);
    }

    public function forUser($user): static
    {
        $this->user = $user;

        try {
            $this->employee = $user->employee;
        } catch (\Throwable $e) {
            $this->employee = null;
        }

        return $this;
    }

    public function getDashboardType(): string
    {
        try {
            $roleNames = $this->user->roles->pluck('name')->toArray();
        } catch (\Throwable $e) {
            return 'employee';
        }

        foreach ($roleNames as $role) {
            if (isset(self::ROLE_MAP[$role])) {
                return self::ROLE_MAP[$role];
            }
        }
        return 'employee';
    }

    /**
     * A widget's `permission` is the primary gate. When a widget carries no
     * permission we fall back to its role list.
     */
    private function canSeeWidget(DashboardWidget $widget): bool
    {
        $permission = $widget->permission;

        if ($permission !== null && $permission !== '') {
            try {
                return Access::can($this->user, $permission);
            } catch (\Throwable $e) {
                // Permission store unavailable - treat as not permitted.
                return false;
            }
        }

        if ($widget->relationLoaded('roles')) {
            $widgetRoles = $widget->roles->pluck('name');
        } elseif ($widget->exists) {
            try {
                $widgetRoles = $widget->roles()->pluck('name');
            } catch (\Throwable $e) {
                $widgetRoles = collect();
            }
        } else {
            // Synthesized default: no role list, so it is shown to everyone.
            $widgetRoles = collect();
        }

        if ($widgetRoles->isEmpty()) {
            return true;
        }

        try {
            $userRoles = $this->user->roles->pluck('name');
        } catch (\Throwable $e) {
            return false;
        }

        return $widgetRoles->intersect($userRoles)->isNotEmpty();
    }

    /**
     * Turn a widget_key such as `pending_leave_requests_mine` into
     * "Pending Leave Requests Mine".
     */
    private static function humanizeKey(string $key): string
    {
        $key = preg_replace('/_(mine|my)$/', '', $key);
        $words = str_replace(['-', '_'], ' ', $key);
        return ucwords($words);
    }

    /**
     * Build an in-memory widget for keys that have no row in
     * dashboard_widgets. This keeps every user on a working dashboard even
     * before the seeder has run.
     */
    private function synthesizeWidget(string $key, array $config): DashboardWidget
    {
        $chartType = null;
        foreach (['charts'] as $bucket) {
            if (in_array($key, $config[$bucket] ?? [], true)) {
                $chartType = 'bar';
            }
        }

        $widget = new DashboardWidget();
        $widget->widget_key = $key;
        $widget->name = self::humanizeKey($key);
        $widget->icon = self::fallbackIcon($key);
        $widget->chart_type = $chartType;
        $widget->permission = null;
        $widget->order = 0;
        $widget->setRelation('roles', collect());

        return $widget;
    }

    /**
     * Load the widget rows for the given keys. Keys with no row at all are
     * replaced with a synthesized default; rows that exist but fail the
     * permission check are dropped and never re-added.
     */
    private function loadWidgets(array $allKeys, array $config): Collection
    {
        try {
            $records = DashboardWidget::where('is_active', true)
                ->whereIn('widget_key', $allKeys)
                ->with('roles')
                ->orderBy('order')
                ->get();
        } catch (\Throwable $e) {
            // Missing/unmigrated dashboard_widgets table - fall through to defaults.
            $records = collect();
        }

        $permitted = $records
            ->filter(fn(DashboardWidget $widget) => $this->canSeeWidget($widget))
            ->keyBy('widget_key');

        $known = $records->keyBy('widget_key');

        foreach ($allKeys as $key) {
            if ($known->has($key)) {
                continue;
            }
            $permitted->put($key, $this->synthesizeWidget($key, $config));
        }

        return $permitted;
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

        $widgets = $this->loadWidgets($allKeys, $config);

        $payload = $this->buildPayload($type, $config, $widgets);

        if ($this->payloadIsEmpty($payload) && $type !== 'employee') {
            // Nothing survived the permission check - guarantee the user a
            // working, self-scoped dashboard rather than an empty page.
            $fallbackConfig = self::DASHBOARD_WIDGETS['employee'];
            $fallbackKeys = array_merge(
                $fallbackConfig['cards'] ?? [],
                $fallbackConfig['quick_actions'] ?? [],
                $fallbackConfig['alerts'] ?? [],
                $fallbackConfig['widgets'] ?? [],
            );
            $payload = $this->buildPayload(
                'employee',
                $fallbackConfig,
                $this->loadWidgets($fallbackKeys, $fallbackConfig)
            );
        }

        return $payload;
    }

    private function payloadIsEmpty(array $payload): bool
    {
        foreach (['cards', 'charts', 'quick_actions', 'alerts', 'widgets'] as $bucket) {
            if (!empty($payload[$bucket])) {
                return false;
            }
        }
        return true;
    }

    private function buildPayload(string $type, array $config, Collection $widgets): array
    {
        $cards = [];
        $charts = [];
        $quickActions = [];
        $alerts = [];
        $widgetsOutput = [];

        // $widgets is already ordered by the seeder-managed `order` column, so
        // each bucket is emitted in database order rather than config order.
        $bucket = fn(string $name): array => $config[$name] ?? [];

        foreach ($widgets as $key => $widget) {
            if (!in_array($key, $bucket('cards'), true)) continue;
            $data = $this->computeWidgetData($widget);
            if ($data === null) continue;
            $cards[] = array_merge([
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'icon' => $widget->icon,
            ], $data);
        }

        foreach ($widgets as $key => $widget) {
            if (!in_array($key, $bucket('charts'), true)) continue;
            $data = $this->computeWidgetData($widget);
            if ($data === null) continue;
            $charts[] = array_merge([
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'icon' => $widget->icon,
                'chart_type' => $widget->chart_type,
            ], $data);
        }

        foreach ($widgets as $key => $widget) {
            if (!in_array($key, $bucket('quick_actions'), true)) continue;
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

        foreach ($widgets as $key => $widget) {
            if (!in_array($key, $bucket('alerts'), true)) continue;
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

        foreach ($widgets as $key => $widget) {
            if (!in_array($key, $bucket('widgets'), true)) continue;
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
        if (!method_exists($this, $method)) {
            return null;
        }

        try {
            return $this->$method();
        } catch (\Throwable $e) {
            // A single unavailable table must not blank the whole dashboard.
            report($e);
            return ['value' => '—', 'subtitle' => 'Unavailable'];
        }
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
            ->whereIn('status', ['present', 'late', 'half-day'])->count();
        return ['value' => $count, 'subtitle' => 'Checked in today'];
    }

    protected function widgetAbsentToday(): array
    {
        $total = Employee::count();
        $present = Attendance::whereDate('date', Carbon::today())
            ->whereIn('status', ['present', 'late', 'half-day'])->count();
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

    /**
     * The `daily_reports` table has no `status` column. Review state is derived
     * from the submitted_at / approved_at timestamps.
     */
    private function dprPendingQuery(): Builder
    {
        return DailyReport::whereNotNull('submitted_at')->whereNull('approved_at');
    }

    private function dprApprovedQuery(): Builder
    {
        return DailyReport::whereNotNull('approved_at');
    }

    protected function widgetPendingApprovals(): array
    {
        $leaves = Leave::where('status', 'Pending')->count();
        $dprs = $this->dprPendingQuery()->count();
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
        $count = Leave::where('status', 'Pending')->count();
        return ['value' => $count, 'subtitle' => 'Awaiting approval'];
    }

    protected function widgetPendingDprApprovals(): array
    {
        $count = $this->dprPendingQuery()->count();
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
            ->where('status', 'Approved')->count();
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
        $latestPeriod = PayrollPeriod::latest('id')->value('id');
        $cost = Payroll::when($latestPeriod, fn($q) => $q->where('payroll_period_id', $latestPeriod))
            ->where('status', 'Approved')
            ->sum('net_salary');
        return ['value' => round($cost, 2), 'prefix' => '₹', 'subtitle' => 'Latest period'];
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
        $count = $this->dprApprovedQuery()->where('employee_id', $this->employee->id)->count();
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
        $count = Leave::where('employee_id', $this->employee->id)->where('status', 'Pending')->count();
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
        $count = \App\Models\InventoryTransfer::where('status', 'pending')->count();
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

    protected function widgetEmployeeSummary(): array
    {
        $active = Employee::where('status', 'active')->count();
        $other = Employee::where('status', '!=', 'active')->count();
        return ['value' => $active, 'subtitle' => "{$active} active, {$other} inactive"];
    }

    protected function widgetAttendanceSummary(): array
    {
        $today = Carbon::today();
        $present = Attendance::whereDate('date', $today)->whereIn('status', ['present', 'late', 'half-day'])->count();
        $absent = Attendance::whereDate('date', $today)->where('status', 'absent')->count();
        $late = Attendance::whereDate('date', $today)->where('status', 'late')->count();
        return ['value' => $present, 'subtitle' => "{$present} present, {$absent} absent, {$late} late"];
    }

    protected function widgetLeaveSummary(): array
    {
        $pending = Leave::where('status', 'Pending')->count();
        $approved = Leave::where('status', 'Approved')->count();
        $rejected = Leave::where('status', 'Rejected')->count();
        return ['value' => $pending, 'subtitle' => "{$pending} pending, {$approved} approved, {$rejected} rejected"];
    }

    protected function widgetDprSummary(): array
    {
        $from = Carbon::now()->startOfMonth()->toDateString();
        $total = DailyReport::where('report_date', '>=', $from)->count();
        $pending = $this->dprPendingQuery()->where('report_date', '>=', $from)->count();
        $approved = $this->dprApprovedQuery()->where('report_date', '>=', $from)->count();
        return ['value' => $total, 'subtitle' => "{$pending} pending, {$approved} approved this month"];
    }

    protected function widgetPayrollSummary(): array
    {
        $total = Payroll::count();
        $draft = Payroll::where('status', 'Draft')->count();
        $approved = Payroll::where('status', 'Approved')->count();
        return ['value' => $total, 'subtitle' => "{$draft} draft, {$approved} approved"];
    }

    protected function widgetPayrollRecords(): array
    {
        $count = Payroll::count();
        $periods = PayrollPeriod::count();
        return ['value' => $count, 'subtitle' => "Across {$periods} payroll period(s)"];
    }

    protected function widgetPayslips(): array
    {
        $count = Payslip::count();
        $generated = Payslip::whereNotNull('generated_at')->count();
        return ['value' => $count, 'subtitle' => "{$generated} generated"];
    }

    protected function widgetPendingPayments(): array
    {
        $count = Invoice::whereIn('status', ['Unpaid', 'Overdue', 'Partially Paid'])->count();
        $overdue = Invoice::where('status', 'Overdue')->count();
        return ['value' => $count, 'subtitle' => "{$overdue} overdue"];
    }

    protected function widgetCompanyOverview(): array
    {
        $settings = CompanySetting::first();
        $name = $settings->company_name ?? config('app.name');
        return ['value' => $name, 'subtitle' => 'Company profile'];
    }

    protected function widgetSitePerformance(): array
    {
        $since = Carbon::now()->subDays(30)->toDateString();
        $sites = Site::where('status', 'Active')->count();
        $withStaff = EmployeeSite::distinct()->count('site_id');
        $reports = DailyReport::whereNotNull('site_id')->where('report_date', '>=', $since)->count();
        return ['value' => $sites, 'subtitle' => "{$withStaff} with staff · {$reports} DPRs (30d)"];
    }

    protected function widgetDepartmentPerformance(): array
    {
        $departments = Department::count();
        $top = Department::withCount('employees')->orderByDesc('employees_count')->first();
        return [
            'value' => $departments,
            'subtitle' => $top ? "Largest: {$top->name} ({$top->employees_count})" : 'No departments',
        ];
    }

    protected function widgetSiteProductivity(): array
    {
        $since = Carbon::now()->subDays(30)->toDateString();
        $reports = DailyReport::whereNotNull('site_id')->where('report_date', '>=', $since)->count();
        $sites = Site::where('status', 'Active')->count();
        $avg = $sites > 0 ? round($reports / $sites, 1) : 0;
        return ['value' => $avg, 'subtitle' => 'Avg DPRs per site (30d)'];
    }

    protected function widgetAssignedEmployees(): array
    {
        $count = EmployeeSite::distinct()->count('employee_id');
        $sites = Site::where('status', 'Active')->count();
        return ['value' => $count, 'subtitle' => "Across {$sites} active site(s)"];
    }

    protected function widgetPendingWorkReports(): array
    {
        $count = $this->dprPendingQuery()->count();
        return ['value' => $count, 'subtitle' => 'Awaiting approval'];
    }

    protected function widgetMyTasks(): array
    {
        if (!$this->employee) {
            return ['value' => 0, 'subtitle' => 'No employee record'];
        }
        $open = Task::where('employee_id', $this->employee->id)->where('status', '!=', 'Completed')->count();
        $total = Task::where('employee_id', $this->employee->id)->count();
        return ['value' => $open, 'subtitle' => "{$total} assigned"];
    }

    protected function widgetTodayAttendance(): array
    {
        if (!$this->employee) {
            return ['value' => '—', 'subtitle' => 'No employee record'];
        }
        $record = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', Carbon::today())->first();

        if (!$record) {
            return ['value' => 'Not marked', 'subtitle' => 'No attendance today'];
        }

        $checkIn = $record->check_in ? Carbon::parse($record->check_in)->format('h:i A') : '—';
        $checkOut = $record->check_out ? Carbon::parse($record->check_out)->format('h:i A') : '—';

        return [
            'value' => ucfirst((string) $record->status),
            'subtitle' => "In {$checkIn} · Out {$checkOut}",
        ];
    }

    protected function widgetAuditLogs(): array
    {
        $since = Carbon::now()->subDays(30);
        $count = ActivityLog::where('created_at', '>=', $since)->count();
        return ['value' => $count, 'subtitle' => 'Events in last 30 days'];
    }

    protected function widgetUserActivity(): array
    {
        $since = Carbon::now()->subDays(7);
        $users = ActivityLog::whereNotNull('user_id')->where('created_at', '>=', $since)
            ->distinct()->count('user_id');
        $events = ActivityLog::where('created_at', '>=', $since)->count();
        return ['value' => $users, 'subtitle' => "{$events} events in last 7 days"];
    }

    protected function widgetServerStatus(): array
    {
        $issues = [];

        if (!$this->databaseIsReachable()) {
            $issues[] = 'database unreachable';
        }

        $pending = $this->pendingMigrationCount();
        if ($pending > 0) {
            $issues[] = "{$pending} pending migration(s)";
        }

        return [
            'value' => $issues ? 'Degraded' : 'Healthy',
            'subtitle' => $issues ? implode(', ', $issues) : 'All checks passed',
        ];
    }

    private function databaseIsReachable(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function pendingMigrationCount(): int
    {
        try {
            $applied = DB::table('migrations')->pluck('migration')->all();
            $files = glob(database_path('migrations') . DIRECTORY_SEPARATOR . '*.php') ?: [];
            $names = array_map(fn($file) => basename($file, '.php'), $files);
            return count(array_diff($names, $applied));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function widgetRecentActivity(): array
    {
        $since = Carbon::now()->subDays(7);
        $events = ActivityLog::where('created_at', '>=', $since)->count();
        $last = ActivityLog::where('created_at', '>=', $since)
            ->orderByDesc('created_at')->value('description');

        return [
            'value' => $events,
            'subtitle' => $last ? Str::limit((string) $last, 60) : 'Events in last 7 days',
        ];
    }

    protected function widgetAttendanceTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $present = Attendance::whereMonth('date', $date->month)->whereYear('date', $date->year)
                ->whereIn('status', ['present', 'late', 'half-day'])
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
                ->whereIn('status', ['present', 'late', 'half-day'])
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
            $count = DailyReport::whereMonth('report_date', $date->month)->whereYear('report_date', $date->year)->count();
            $data[] = ['name' => $date->format('M'), 'value' => $count];
        }
        return ['data' => $data, 'chart_type' => 'bar'];
    }

    protected function widgetWorkforceUtilization(): array
    {
        $total = Employee::count();
        $present = Attendance::whereDate('date', Carbon::today())->whereIn('status', ['present', 'late', 'half-day'])->count();
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
        return ['label' => 'Manage Users', 'link' => '/dashboard/access-control'];
    }

    protected function widgetManageAccess(): array
    {
        return ['label' => 'Manage Access', 'link' => '/dashboard/access-control'];
    }

    protected function widgetManagePermissions(): array
    {
        return ['label' => 'Manage Permissions', 'link' => '/dashboard/permissions'];
    }

    protected function widgetSystemSettings(): array
    {
        return ['label' => 'System Settings', 'link' => '/dashboard/settings'];
    }

    protected function widgetGeneratePayslip(): array
    {
        return ['label' => 'Generate Payslip', 'link' => '/dashboard/my-payroll'];
    }

    protected function widgetExportPayroll(): array
    {
        return ['label' => 'Export Payroll', 'link' => '/dashboard/process-payroll'];
    }

    protected function widgetReviewAttendance(): array
    {
        return ['label' => 'Review Attendance', 'link' => '/dashboard/attendance'];
    }

    protected function widgetCheckIn(): array
    {
        return ['label' => 'Check In', 'link' => '/dashboard/my-attendance'];
    }

    protected function widgetCheckOut(): array
    {
        return ['label' => 'Check Out', 'link' => '/dashboard/my-attendance'];
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

    protected function widgetSiteDelaysAlert(): ?array
    {
        $delayed = Site::where('status', 'Active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', Carbon::today())
            ->count();

        if ($delayed === 0) return null;

        return [
            'value' => "{$delayed} sites past end date",
            'subtitle' => 'Review schedule or extend',
            'severity' => 'warning',
        ];
    }

    protected function widgetServerAlert(): ?array
    {
        $issues = [];

        if (!$this->databaseIsReachable()) {
            $issues[] = 'database unreachable';
        }

        $pending = $this->pendingMigrationCount();
        if ($pending > 0) {
            $issues[] = "{$pending} pending migration(s)";
        }

        if (!$issues) return null;

        return [
            'value' => 'System issues detected',
            'subtitle' => implode(' · ', $issues),
            'severity' => in_array('database unreachable', $issues, true) ? 'critical' : 'warning',
        ];
    }

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
            ->whereIn('status', ['present', 'late', 'half-day'])->count();
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
        $submitted = DailyReport::where('employee_id', $this->employee->id)->whereDate('report_date', Carbon::today())->exists();
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
        $present = Attendance::where('employee_id', $this->employee->id)->whereIn('status', ['present', 'late', 'half-day'])
            ->whereMonth('date', Carbon::now()->month)->count();
        return ['type' => 'mini_card', 'value' => $present, 'subtitle' => 'Days this month'];
    }

    protected function widgetMyDpr(): ?array
    {
        if (!$this->employee) return null;
        $count = DailyReport::where('employee_id', $this->employee->id)->whereMonth('report_date', Carbon::now()->month)->count();
        return ['type' => 'mini_card', 'value' => $count, 'subtitle' => 'Reports this month'];
    }

    protected function widgetMyDocuments(): ?array
    {
        if (!$this->employee) return null;
        $count = $this->employee->documents()->count();
        return ['type' => 'mini_card', 'value' => $count, 'subtitle' => 'Documents on file'];
    }

    protected function widgetMyPayslips(): ?array
    {
        if (!$this->employee) return null;
        $count = \App\Models\Payslip::whereHas('payroll', fn($q) => $q->where('employee_id', $this->employee->id))->count();
        return ['type' => 'mini_card', 'value' => $count, 'subtitle' => 'Payslips available'];
    }
}
