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
        $query = Payroll::with(['employee', 'payrollPeriod', 'payslip']);
        
        if ($request->has('payroll_period_id')) {
            $query->where('payroll_period_id', $request->payroll_period_id);
        }
        
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function show(Payroll $payroll)
    {
        return response()->json($payroll->load(['employee.department', 'employee.designation', 'payrollPeriod', 'payslip']));
    }

    public function generate(Request $request)
    {
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

        $query = Employee::query()->where('status', 'active');

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
                // Prevent duplicate generation unless regenerate is called
                if (Payroll::where('employee_id', $employee->id)->where('payroll_period_id', $period->id)->exists()) {
                    continue;
                }

                try {
                    $payrollData = $this->payrollService->calculate($employee, $period);
                    Payroll::create($payrollData);
                    $generatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Employee ID {$employee->id}: " . $e->getMessage();
                }
            }
            DB::commit();
            Log::info("Payroll generation completed", [
                'period_id' => $period->id,
                'generated_count' => $generatedCount,
                'user_id' => auth()->id() ?? 'system'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Payroll generation failed.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => "Successfully generated payroll for {$generatedCount} employees.",
            'errors' => $errors
        ]);
    }

    public function regenerate(Request $request)
    {
        $request->validate([
            'payroll_id' => 'required|exists:payrolls,id',
        ]);

        $payroll = Payroll::findOrFail($request->payroll_id);
        
        if ($payroll->status !== 'Draft') {
            return response()->json(['message' => 'Can only regenerate Draft payrolls.'], 403);
        }

        try {
            $payrollData = $this->payrollService->calculate($payroll->employee, $payroll->payrollPeriod);
            $payroll->update($payrollData);
            return response()->json(['message' => 'Payroll regenerated successfully.', 'payroll' => $payroll->fresh()]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Regeneration failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, Payroll $payroll)
    {
        $payroll->update(['status' => 'Approved']);
        Log::info("Payroll approved", ['payroll_id' => $payroll->id, 'user_id' => auth()->id() ?? 'system']);
        return response()->json($payroll);
    }

    public function lock(Request $request, Payroll $payroll)
    {
        $payroll->update(['status' => 'Locked']);
        Log::info("Payroll locked", ['payroll_id' => $payroll->id, 'user_id' => auth()->id() ?? 'system']);
        return response()->json($payroll);
    }
}
