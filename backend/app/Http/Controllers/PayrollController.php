<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Services\PayrollCalculationService;
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
        $this->authorize('payroll.view');

        $query = Payroll::with(['employee', 'payrollPeriod', 'payslip']);

        if ($request->has('payroll_period_id')) {
            $query->where('payroll_period_id', $request->payroll_period_id);
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
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
        $this->authorize('payroll.view');

        return response()->json($payroll->load(['employee.department', 'employee.designation', 'payrollPeriod', 'payslip']));
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

        return response()->json([
            'message' => "Payroll generated for {$generatedCount} employees.",
            'generated_count' => $generatedCount,
            'errors' => $errors,
        ]);
    }

    public function regenerate(Request $request)
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
