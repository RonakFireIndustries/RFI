<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    public function index()
    {
        $this->authorize('manage_payroll');
        return response()->json(PayrollPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get());
    }

    public function store(Request $request)
    {
        $this->authorize('manage_payroll');
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:Draft,Processing,Approved,Locked,Paid',
        ]);

        // Prevent duplicate periods
        if (PayrollPeriod::where('month', $validated['month'])->where('year', $validated['year'])->exists()) {
            return response()->json(['message' => 'Payroll period for this month and year already exists.'], 422);
        }

        $period = PayrollPeriod::create($validated);
        return response()->json($period, 201);
    }

    public function show(PayrollPeriod $payrollPeriod)
    {
        return response()->json($payrollPeriod);
    }

    public function update(Request $request, PayrollPeriod $payrollPeriod)
    {
        $this->authorize('manage_payroll');
        $validated = $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'status' => 'sometimes|string|in:Draft,Processing,Approved,Locked,Paid',
        ]);

        $payrollPeriod->update($validated);
        return response()->json($payrollPeriod);
    }

    public function destroy(PayrollPeriod $payrollPeriod)
    {
        $this->authorize('manage_payroll');
        if (in_array($payrollPeriod->status, ['Locked', 'Paid'])) {
            return response()->json(['message' => 'Cannot delete a payroll period with Locked or Paid status.'], 403);
        }
        $payrollPeriod->delete();
        return response()->noContent();
    }
}
