<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        $this->authorize('view_payroll');

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
        $this->authorize('view_payroll');

        return response()->json($payroll->load(['employee.department', 'employee.designation', 'payrollPeriod', 'payslip']));
    }

    public function generate(Request $request)
    {
        $this->authorize('manage_payroll');

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
            $query->whereHas('sites', function ($q) use ($request) {
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
                    $result = $this->payrollService->calculatePayroll($employee, $period);
                    Payroll::create([
                        'employee_id' => $employee->id,
                        'payroll_period_id' => $period->id,
                        'basic_pay' => $result['basic_pay'],
                        'allowances' => json_encode($result['allowances']),
                        'deductions' => json_encode($result['deductions']),
                        'net_pay' => $result['net_pay'],
                        'status' => 'Generated',
                    ]);
                    $generatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Employee {$employee->id}: {$e->getMessage()}";
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
        $this->authorize('manage_payroll');

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
                    $result = $this->payrollService->calculatePayroll($employee, $period);
                    $payroll->update([
                        'basic_pay' => $result['basic_pay'],
                        'allowances' => json_encode($result['allowances']),
                        'deductions' => json_encode($result['deductions']),
                        'net_pay' => $result['net_pay'],
                    ]);
                    $regeneratedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Payroll {$payroll->id}: {$e->getMessage()}";
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Payroll regeneration failed', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => "Payroll regenerated for {$regeneratedCount} employees.",
            'regenerated_count' => $regeneratedCount,
            'errors' => $errors,
        ]);
    }

    public function approve($payroll)
    {
        $this->authorize('manage_payroll');

        $payroll = Payroll::findOrFail($payroll);
        $payroll->status = 'Approved';
        $payroll->approved_at = now();
        $payroll->save();

        return response()->json($payroll);
    }

    public function lock($payroll)
    {
        $this->authorize('manage_payroll');

        $payroll = Payroll::findOrFail($payroll);
        $payroll->status = 'Locked';
        $payroll->save();

        return response()->json($payroll);
    }

    public function unlock($payroll)
    {
        $this->authorize('manage_payroll');

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
        $this->authorize('manage_payroll');

        $payroll = Payroll::findOrFail($payroll);
        $payroll->delete();

        return response()->json(['message' => 'Payroll deleted']);
    }
}
