<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Services\PayrollCalculationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PayrollController extends Controller
{
    /** @var array<int,string>|null Cached payrolls column names. */
    protected static ?array $payrollColumns = null;

    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        // Self-service: an employee may always read their OWN payroll, even
        // without the payroll.view permission. Anyone listing other employees'
        // payroll still needs it.
        $requestedEmployeeId = $request->input('employee_id');
        $ownEmployeeId = $request->user()?->employee?->id;
        $ownRecordOnly = $requestedEmployeeId
            && $ownEmployeeId
            && (int) $requestedEmployeeId === (int) $ownEmployeeId;

        if (!$ownRecordOnly) {
            $this->authorize('payroll.view');
        }

        $query = Payroll::with(['employee', 'payrollPeriod', 'payslip']);

        if ($request->has('payroll_period_id')) {
            $query->where('payroll_period_id', $request->payroll_period_id);
        }

        if ($requestedEmployeeId) {
            $query->where('employee_id', $requestedEmployeeId);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function myPayroll(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'No employee profile found'], 404);
        }

        $payrolls = Payroll::with(['payrollPeriod', 'payslip'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payrolls);
    }

    public function show(Payroll $payroll)
    {
        $ownEmployeeId = request()->user()?->employee?->id;

        // Self-service: an employee may open their own payroll record.
        if (!$ownEmployeeId || (int) $payroll->employee_id !== (int) $ownEmployeeId) {
            $this->authorize('payroll.view');
        }

        $payroll->load(['employee.department', 'employee.designation', 'payrollPeriod', 'payslip']);

        $data = $payroll->toArray();

        // Attendance breakdown behind the numbers, so a payroll report opened
        // directly (before a payslip row exists) explains itself the same way
        // the payslip does.
        if ($payroll->employee && $payroll->payrollPeriod) {
            $data['breakdown'] = app(PayrollCalculationService::class)
                ->summarizeAttendance($payroll->employee, $payroll->payrollPeriod);
        }

        return response()->json($data);
    }

    public function generate(Request $request)
    {
        $this->authorize('payroll.manage');

        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'employee_id' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        $period = PayrollPeriod::findOrFail($request->payroll_period_id);

        if ($period->status === 'Locked' || $period->status === 'Paid') {
            return response()->json(['message' => 'Cannot generate payroll for a locked or paid period.'], 403);
        }

        $query = Employee::query();

        if ($request->has('employee_id')) {
            $query->where('id', $request->employee_id);
        } elseif ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        } elseif ($request->has('site_id')) {
            // Employee has no direct `sites` relation: site membership lives in
            // employee_site (EmployeeSite). Using the wrong relation name makes
            // Laravel throw RelationNotFoundException -> 500.
            $query->whereHas('employeeSites', function ($q) use ($request) {
                $q->where('site_id', $request->site_id);
            });
        }

        $employees = $query->get();
        $generatedCount = 0;
        $errors = [];
        $warnings = [];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                if (Payroll::where('employee_id', $employee->id)->where('payroll_period_id', $period->id)->exists()) {
                    continue;
                }

                try {
                    $result = $this->payrollService->calculate($employee, $period);
                    Payroll::create($this->payrollAttributes($result, $period, 'Generated'));
                    $generatedCount++;

                    if ((float) ($result['paid_days'] ?? 0) <= 0) {
                        $warnings[] = sprintf(
                            'Employee %d (%s): no paid attendance in this period - net pay is 0.',
                            $employee->id,
                            $employee->full_name ?? $employee->emp_id ?? '—'
                        );
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Employee {$employee->id}: {$e->getMessage()}";
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payroll generation failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Payroll generation failed', 'error' => $e->getMessage()], 500);
        }

        // Payroll rows are one per employee, so a run over 500 people must
        // produce ONE notification for the payroll team, not 500.
        $periodLabel = $this->periodLabel($period);

        NotificationService::notify('payroll.generated', [
            'id' => $period->id,
            'period' => $periodLabel,
            'count' => $generatedCount,
        ], $request->user()->id);

        if ($warnings !== []) {
            NotificationService::notify('payroll.completed_with_warnings', [
                'id' => $period->id,
                'period' => $periodLabel,
                'count' => count($warnings),
            ], $request->user()->id);
        }

        return response()->json([
            'message' => "Payroll generated for {$generatedCount} employees.",
            'generated_count' => $generatedCount,
            'errors' => $errors,
            'warnings' => $warnings,
        ]);
    }    public function regenerate(Request $request)
    {
        $this->authorize('payroll.manage');

        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $period = PayrollPeriod::findOrFail($request->payroll_period_id);

        if ($period->status === 'Locked' || $period->status === 'Paid') {
            return response()->json(['message' => 'Cannot regenerate payroll for a locked or paid period.'], 403);
        }

        $query = Payroll::where('payroll_period_id', $period->id);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $payrolls = $query->get();
        $regeneratedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($payrolls as $payroll) {
                try {
                    $employee = Employee::findOrFail($payroll->employee_id);
                    $result = $this->payrollService->calculate($employee, $period);
                    $payroll->fill($this->payrollAttributes($result, $period, $payroll->status))->save();
                    $regeneratedCount++;
                } catch (\Throwable $e) {
                    $errors[] = "Payroll {$payroll->id}: {$e->getMessage()}";
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payroll regeneration failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Payroll regeneration failed', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => "Payroll regenerated for {$regeneratedCount} employees.",
            'regenerated_count' => $regeneratedCount,
            'errors' => $errors,
        ]);
    }

    /**
     * Turn a PayrollCalculationService result into an attribute array that the
     * Payroll model can mass-assign.
     *
     * The service returns every calculated column (net_salary, hra, pf, month,
     * year, ...) plus a couple of diagnostic values (late_marks,
     * late_deduction_days) that are not columns. Model::create()/fill() drop
     * anything that is not fillable, but a key that *is* fillable while the
     * column is missing from the table makes the INSERT fail, so the payload is
     * intersected with the real schema first.
     *
     * `month_year` is a legacy NOT NULL column with no default on some
     * installs: without it every insert fails with
     * "Column 'month_year' cannot be null".
     */
    /**
     * A human label for a payroll period, tolerating whichever columns the
     * install actually has.
     */
    protected function periodLabel(PayrollPeriod $period): string
    {
        if (! empty($period->name)) {
            return (string) $period->name;
        }

        $parts = array_filter([
            $period->month ?? null,
            $period->year ?? null,
            $period->period_start ?? $period->start_date ?? null,
            $period->period_end ?? $period->end_date ?? null,
        ]);

        if ($parts === []) {
            return 'the current period';
        }

        if (isset($period->month, $period->year)) {
            try {
                return \Illuminate\Support\Carbon::createFromDate($period->year, $period->month, 1)->format('M Y');
            } catch (\Throwable $e) {
                return "{$period->month}/{$period->year}";
            }
        }

        return implode(' to ', array_map(
            fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('d M Y'),
            array_slice($parts, -2)
        ));
    }

    protected function payrollAttributes(array $result, PayrollPeriod $period, string $status): array
    {
        $result['status'] = $status;

        $columns = static::$payrollColumns ??= Schema::getColumnListing('payrolls');
        $attributes = array_intersect_key($result, array_flip($columns));

        if (in_array('month_year', $columns, true)) {
            $attributes['month_year'] = sprintf('%04d-%02d', (int) $period->year, (int) $period->month);
        }

        return $attributes;
    }

    public function approve($payroll)
    {
        $this->authorize('payroll.manage');

        $payroll = Payroll::findOrFail($payroll);
        $payroll->status = 'Approved';
        // approved_at is not part of the payrolls schema on every install;
        // only stamp it when the column exists so approve() never 500s.
        if (Schema::hasColumn('payrolls', 'approved_at')) {
            $payroll->approved_at = now();
        }
        $payroll->save();

        // Approval happens one row at a time but really means "this period is
        // signed off", so the dedupe key collapses a full-period sweep into a
        // single alert instead of one per employee.
        $periodId = $payroll->payroll_period_id;
        $period = $periodId ? PayrollPeriod::find($periodId) : null;

        NotificationService::notify('payroll.approved', [
            'id' => $periodId,
            'period' => $period ? $this->periodLabel($period) : 'the current period',
        ], auth()->id(), null, '/dashboard/payroll', "payroll.approved:{$periodId}");

        return response()->json($payroll);
    }

    public function lock($payroll)
    {
        $this->authorize('payroll.manage');

        $payroll = Payroll::findOrFail($payroll);
        $payroll->status = 'Locked';
        $payroll->save();

        return response()->json($payroll);
    }

    public function unlock($payroll)
    {
        $this->authorize('payroll.manage');

        $payroll = Payroll::findOrFail($payroll);

        if ($payroll->status === 'Paid') {
            return response()->json(['message' => 'Cannot unlock a paid payroll.'], 403);
        }

        $payroll->status = 'Generated';
        $payroll->save();

        return response()->json($payroll);
    }

    public function destroy($payroll)
    {
        $this->authorize('payroll.manage');

        $payroll = Payroll::findOrFail($payroll);
        $payroll->delete();

        return response()->json(['message' => 'Payroll deleted']);
    }
}
