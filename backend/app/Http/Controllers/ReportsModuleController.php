<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportSchedule;
use App\Models\ReportGeneration;
use App\Http\Resources\ReportCategoryResource;
use App\Http\Resources\ReportResource;
use App\Http\Resources\ReportScheduleResource;
use App\Http\Resources\ReportGenerationResource;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\StoreReportScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function categories()
    {
        $this->authorize('view reports');
        $categories = ReportCategory::where('is_active', true)
            ->withCount('activeReports')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ReportCategoryResource::collection($categories),
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('view reports');
        $query = Report::with('category', 'creator')
            ->withCount(['schedules', 'activeSchedules', 'generations']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('parameters->branch_id', $request->branch_id);
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $reports = $query->orderBy('sort_order')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function store(StoreReportRequest $request)
    {
        $this->authorize('create reports');
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        if (isset($data['parameters']) && is_string($data['parameters'])) {
            $data['parameters'] = json_decode($data['parameters'], true);
        }

        $report = Report::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Report created successfully',
            'data' => new ReportResource($report->load('category', 'creator')),
        ], 201);
    }

    public function show(Report $report)
    {
        $this->authorize('view reports');
        $report->load(['category', 'creator', 'schedules', 'generations' => function ($q) {
            $q->latest('generated_at')->limit(5);
        }]);

        return response()->json([
            'success' => true,
            'data' => new ReportResource($report),
        ]);
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        $this->authorize('update reports');
        $data = $request->validated();

        if (isset($data['parameters']) && is_string($data['parameters'])) {
            $data['parameters'] = json_decode($data['parameters'], true);
        }

        $report->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Report updated successfully',
            'data' => new ReportResource($report->load('category', 'creator')),
        ]);
    }

    public function destroy(Report $report)
    {
        if (!Auth::user()->can('delete_reports')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully',
        ]);
    }

    public function generations(Request $request)
    {
        $this->authorize('view reports');
        $query = ReportGeneration::with('report.category', 'generator');

        if ($request->filled('report_id')) {
            $query->where('report_id', $request->report_id);
        }

        $perPage = min((int) $request->input('per_page', 10), 50);
        $generations = $query->latest('generated_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ReportGenerationResource::collection($generations),
            'meta' => [
                'current_page' => $generations->currentPage(),
                'last_page' => $generations->lastPage(),
                'per_page' => $generations->perPage(),
                'total' => $generations->total(),
            ],
        ]);
    }

    public function generate(Request $request, Report $report)
    {
        if (!Auth::user()->can('export_reports')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $fileName = $report->slug . '_' . now()->format('Ymd_His') . '.pdf';
        $filePath = 'reports/' . $fileName;
        $params = $request->only(['start_date', 'end_date', 'branch_id']);

        $viewData = $this->getReportViewData($report, $params);

        $viewName = 'reports.' . str_replace('-', '_', $report->slug);
        if (!view()->exists($viewName)) {
            $viewName = 'reports.generic';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, [
            'report' => $report,
            'params' => $params,
            'data' => $viewData,
            'generated_at' => now()->format('d-m-Y H:i:s'),
            'generated_by' => Auth::user()->name,
        ]);

        \Storage::put($filePath, $pdf->output());

        $generation = ReportGeneration::create([
            'report_id' => $report->id,
            'file_name' => $fileName,
            'file_type' => 'pdf',
            'generated_by' => Auth::id(),
            'generated_at' => now(),
            'parameters' => $params,
            'status' => 'completed',
            'file_size' => \Storage::size($filePath),
            'file_path' => $filePath,
        ]);

        $report->update(['last_generated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully',
            'data' => new ReportGenerationResource($generation->load('report', 'generator')),
        ]);
    }

    private function getReportViewData(Report $report, array $params): array
    {
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        return match ($report->slug) {
            'sales-overview' => $this->dataSalesOverview($startDate, $endDate, $branchId),
            'sales-by-customer' => $this->dataSalesByCustomer($startDate, $endDate, $branchId),
            'revenue-report' => $this->dataRevenueReport($startDate, $endDate, $branchId),
            'stock-summary' => $this->dataStockSummary($branchId),
            'stock-movement' => $this->dataStockMovement($startDate, $endDate, $branchId),
            'inventory-valuation' => $this->dataInventoryValuation($branchId),
            'payment-report' => $this->dataPaymentReport($startDate, $endDate),
            'invoice-summary' => $this->dataInvoiceSummary($startDate, $endDate),
            'employee-directory' => $this->dataEmployeeDirectory(),
            'department-overview' => $this->dataDepartmentOverview(),
            'employee-count-report' => $this->dataEmployeeCount(),
            'attendance-report' => $this->dataAttendanceReport($startDate, $endDate),
            'attendance-summary' => $this->dataAttendanceSummary($startDate, $endDate),
            'payroll-summary' => $this->dataPayrollSummary($startDate, $endDate),
            'salary-structure-report' => $this->dataSalaryStructure(),
            'purchase-orders' => $this->dataPurchaseOrders($startDate, $endDate),
            'supplier-report' => $this->dataSupplierReport(),
            'leave-report' => $this->dataLeaveReport($startDate, $endDate),
            'leave-balance' => $this->dataLeaveBalance(),
            default => [],
        };
    }

    private function dataLeaveBalance(): array
    {
        $balances = \App\Models\LeaveBalance::with(['employee', 'leaveType'])
            ->where('year', date('Y'))
            ->orderBy('employee_id')
            ->get();

        $summary = [
            'total_employees' => $balances->pluck('employee_id')->unique()->count(),
            'total_allocated' => $balances->sum('allocated'),
            'total_used' => $balances->sum('used'),
            'total_remaining' => $balances->sum('remaining'),
        ];

        return compact('balances', 'summary');
    }

    private function dataSalesOverview($startDate, $endDate, $branchId): array
    {
        $query = \App\Models\SalesOrder::with('customer');
        if ($startDate) $query->whereDate('order_date', '>=', $startDate);
        if ($endDate) $query->whereDate('order_date', '<=', $endDate);

        $orders = $query->orderByDesc('order_date')->get();
        $totalAmount = $orders->sum('grand_total');
        $totalOrders = $orders->count();

        return compact('orders', 'totalAmount', 'totalOrders');
    }

    private function dataSalesByCustomer($startDate, $endDate, $branchId): array
    {
        $query = \App\Models\SalesOrder::with('customer');
        if ($startDate) $query->whereDate('order_date', '>=', $startDate);
        if ($endDate) $query->whereDate('order_date', '<=', $endDate);

        $grouped = $query->get()->groupBy(fn($o) => $o->customer->name ?? 'Unknown');
        $customerData = $grouped->map(fn($orders, $name) => [
            'customer' => $name,
            'count' => $orders->count(),
            'total' => $orders->sum('grand_total'),
        ])->sortByDesc('total')->values();

        return compact('customerData');
    }

    private function dataRevenueReport($startDate, $endDate, $branchId): array
    {
        $query = \App\Models\Invoice::with('customer');
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $invoices = $query->orderByDesc('created_at')->get();
        $totalRevenue = $invoices->sum('grand_total');
        $totalInvoices = $invoices->count();
        $paidInvoices = $invoices->where('status', 'paid')->count();
        $paidAmount = $invoices->where('status', 'paid')->sum('grand_total');
        $pendingAmount = $invoices->whereIn('status', ['draft', 'sent', 'overdue'])->sum('grand_total');

        return compact('invoices', 'totalRevenue', 'totalInvoices', 'paidInvoices', 'paidAmount', 'pendingAmount');
    }

    private function dataStockSummary($branchId): array
    {
        $stocks = \App\Models\ProductStock::with('product', 'locationable')
            ->orderBy('product_id')
            ->get();

        $totalQty = $stocks->sum('quantity');
        $totalProducts = $stocks->pluck('product_id')->unique()->count();

        return compact('stocks', 'totalQty', 'totalProducts');
    }

    private function dataStockMovement($startDate, $endDate, $branchId): array
    {
        $query = \App\Models\TransactionLedger::with('product', 'locationable', 'toLocationable');
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $transactions = $query->orderByDesc('created_at')->limit(200)->get();
        $totalTransactions = $transactions->count();

        return compact('transactions', 'totalTransactions');
    }

    private function dataInventoryValuation($branchId): array
    {
        $stocks = \App\Models\ProductStock::with('product')
            ->where('quantity', '>', 0)
            ->orderByDesc('quantity')
            ->get();

        $totalValue = $stocks->sum(fn($s) => ($s->product->selling_price ?? 0) * $s->quantity);
        $totalItems = $stocks->sum('quantity');

        return compact('stocks', 'totalValue', 'totalItems');
    }

    private function dataPaymentReport($startDate, $endDate): array
    {
        $query = \App\Models\Payment::query();
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $payments = $query->orderByDesc('created_at')->get();
        $totalAmount = $payments->sum('amount');
        $totalPayments = $payments->count();

        return compact('payments', 'totalAmount', 'totalPayments');
    }

    private function dataInvoiceSummary($startDate, $endDate): array
    {
        $query = \App\Models\Invoice::with('customer');
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $invoices = $query->orderByDesc('created_at')->get();
        $totalAmount = $invoices->sum('grand_total');
        $counts = $invoices->groupBy('status')->map->count();

        return compact('invoices', 'totalAmount', 'counts');
    }

    private function dataEmployeeDirectory(): array
    {
        $employees = \App\Models\Employee::with('department', 'designation')
            ->orderBy('full_name')
            ->get();

        $total = $employees->count();
        $deptCount = $employees->groupBy(fn($e) => $e->department->name ?? 'N/A')->map->count();

        return compact('employees', 'total', 'deptCount');
    }

    private function dataDepartmentOverview(): array
    {
        $departments = \App\Models\Department::withCount('employees')
            ->orderBy('name')
            ->get();

        $totalEmployees = $departments->sum('employees_count');
        $totalDepartments = $departments->count();

        return compact('departments', 'totalEmployees', 'totalDepartments');
    }

    private function dataEmployeeCount(): array
    {
        $total = \App\Models\Employee::count();
        $byDepartment = \App\Models\Department::withCount('employees')
            ->orderByDesc('employees_count')
            ->get()
            ->pluck('employees_count', 'name');

        $byType = \App\Models\Employee::selectRaw('employment_type, count(*) as cnt')
            ->groupBy('employment_type')
            ->pluck('cnt', 'employment_type');

        return compact('total', 'byDepartment', 'byType');
    }

    private function dataAttendanceReport($startDate, $endDate): array
    {
        $query = \App\Models\Attendance::with('employee', 'site', 'shift');
        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate) $query->whereDate('date', '<=', $endDate);

        $records = $query->orderByDesc('date')->limit(200)->get();
        $totalRecords = $records->count();

        return compact('records', 'totalRecords');
    }

    private function dataAttendanceSummary($startDate, $endDate): array
    {
        $query = \App\Models\Attendance::selectRaw('employee_id, count(*) as total_days, sum(working_hours) as total_hours, status')
            ->groupBy('employee_id', 'status')
            ->with('employee');
        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate) $query->whereDate('date', '<=', $endDate);

        $records = $query->get()->groupBy(fn($r) => $r->employee->full_name ?? 'Unknown');

        return compact('records');
    }

    private function dataPayrollSummary($startDate, $endDate): array
    {
        $query = \App\Models\Payroll::with('employee');
        if ($startDate) $query->where('year', date('Y', strtotime($startDate)));
        if ($endDate) $query->where('month', '>=', date('m', strtotime($startDate)));

        $payrolls = $query->orderByDesc('year')->orderByDesc('month')->get();
        $totalGross = $payrolls->sum(fn($p) => $p->basic_salary + $p->hra + $p->conveyance + $p->special_allowance + $p->other_allowance);
        $totalNet = $payrolls->sum('net_salary');
        $totalCount = $payrolls->count();

        return compact('payrolls', 'totalGross', 'totalNet', 'totalCount');
    }

    private function dataSalaryStructure(): array
    {
        $structures = \App\Models\SalaryStructure::with('employee')
            ->where('status', 'active')
            ->orderBy('employee_id')
            ->get();

        $totalCount = $structures->count();

        return compact('structures', 'totalCount');
    }

    private function dataPurchaseOrders($startDate, $endDate): array
    {
        $query = \App\Models\PurchaseOrder::with('supplier');
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $orders = $query->orderByDesc('created_at')->get();
        $totalAmount = $orders->sum('grand_total');
        $totalOrders = $orders->count();

        return compact('orders', 'totalAmount', 'totalOrders');
    }

    private function dataSupplierReport(): array
    {
        $suppliers = \App\Models\Supplier::withCount('purchaseOrders')
            ->orderBy('name')
            ->get();

        $totalSuppliers = $suppliers->count();

        return compact('suppliers', 'totalSuppliers');
    }

    private function dataLeaveReport($startDate, $endDate): array
    {
        $query = \App\Models\Leave::with('employee', 'leaveType');
        if ($startDate) $query->whereDate('start_date', '>=', $startDate);
        if ($endDate) $query->whereDate('end_date', '<=', $endDate);

        $leaves = $query->orderByDesc('start_date')->get();
        $totalLeaves = $leaves->count();
        $pendingCount = $leaves->where('status', 'pending')->count();
        $approvedCount = $leaves->where('status', 'approved')->count();

        return compact('leaves', 'totalLeaves', 'pendingCount', 'approvedCount');
    }

    public function downloadGeneration(ReportGeneration $generation)
    {
        if (!Auth::user()->can('export_reports')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$generation->file_path || !\Storage::exists($generation->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return \Storage::download($generation->file_path, $generation->file_name);
    }

    public function schedules(Request $request)
    {
        $this->authorize('view reports');
        $query = ReportSchedule::with('report.category', 'creator');

        if ($request->filled('report_id')) {
            $query->where('report_id', $request->report_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('next_run_at')->get();

        return response()->json([
            'success' => true,
            'data' => ReportScheduleResource::collection($schedules),
        ]);
    }

    public function storeSchedule(StoreReportScheduleRequest $request)
    {
        $this->authorize('schedule reports');
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        if (isset($data['config']) && is_string($data['config'])) {
            $data['config'] = json_decode($data['config'], true);
        }

        $schedule = ReportSchedule::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully',
            'data' => new ReportScheduleResource($schedule->load('report', 'creator')),
        ], 201);
    }

    public function updateSchedule(Request $request, ReportSchedule $schedule)
    {
        if (!Auth::user()->can('schedule_reports')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|in:daily,weekly,monthly,quarterly,yearly,custom',
            'config' => 'nullable|json',
            'next_run_at' => 'nullable|date',
            'recipients' => 'nullable|string',
            'format' => 'nullable|string|in:pdf,csv,xlsx',
            'status' => 'nullable|string|in:active,paused,completed,failed',
        ]);

        if (isset($validated['config']) && is_string($validated['config'])) {
            $validated['config'] = json_decode($validated['config'], true);
        }

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully',
            'data' => new ReportScheduleResource($schedule->load('report', 'creator')),
        ]);
    }

    public function destroySchedule(ReportSchedule $schedule)
    {
        if (!Auth::user()->can('schedule_reports')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully',
        ]);
    }

    public function toggleSchedule(ReportSchedule $schedule)
    {
        if (!Auth::user()->can('schedule_reports')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schedule->update([
            'status' => $schedule->status === 'active' ? 'paused' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Schedule ' . ($schedule->status === 'active' ? 'activated' : 'paused') . ' successfully',
            'data' => new ReportScheduleResource($schedule->load('report', 'creator')),
        ]);
    }

    public function stats()
    {
        $this->authorize('view reports');
        $totalReports = Report::where('status', 'active')->count();
        $totalCategories = ReportCategory::where('is_active', true)->count();
        $totalSchedules = ReportSchedule::where('status', 'active')->count();
        $totalGenerations = ReportGeneration::count();
        $recentGenerations = ReportGeneration::with('report', 'generator')
            ->latest('generated_at')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_reports' => $totalReports,
                'total_categories' => $totalCategories,
                'total_active_schedules' => $totalSchedules,
                'total_generations' => $totalGenerations,
                'recent_generations' => ReportGenerationResource::collection($recentGenerations),
            ],
        ]);
    }
}
