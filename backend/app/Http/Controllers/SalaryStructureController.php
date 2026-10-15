<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use App\Support\StatutoryDeductions;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{
    public function index()
    {
        $this->authorize('payroll.manage');
        return response()->json(SalaryStructure::with('employee')->get());
    }

    public function store(Request $request)
    {
        $this->authorize('payroll.manage');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'nullable|numeric|min:0',
            'conveyance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'special_allowance' => 'nullable|numeric|min:0',
            'site_allowance' => 'nullable|numeric|min:0',
            'travel_allowance' => 'nullable|numeric|min:0',
            'food_allowance' => 'nullable|numeric|min:0',
            'other_earnings' => 'nullable|numeric|min:0',
            'pf_deduction' => 'nullable|numeric|min:0',
            'esic_deduction' => 'nullable|numeric|min:0',
            'professional_tax' => 'nullable|numeric|min:0',
            'tds' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'auto_statutory' => 'nullable|boolean',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'status' => 'nullable|string',
        ]);

        $structure = SalaryStructure::create($validated);
        $this->syncStatutory($structure);

        return response()->json($structure->fresh()->load('employee'), 201);
    }

    public function show(SalaryStructure $salaryStructure)
    {
        return response()->json($salaryStructure->load('employee'));
    }

    public function update(Request $request, SalaryStructure $salaryStructure)
    {
        $this->authorize('payroll.manage');
        $validated = $request->validate([
            'employee_id' => 'sometimes|exists:employees,id',
            'basic_salary' => 'sometimes|numeric|min:0',
            'hra' => 'nullable|numeric|min:0',
            'conveyance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'special_allowance' => 'nullable|numeric|min:0',
            'site_allowance' => 'nullable|numeric|min:0',
            'travel_allowance' => 'nullable|numeric|min:0',
            'food_allowance' => 'nullable|numeric|min:0',
            'other_earnings' => 'nullable|numeric|min:0',
            'pf_deduction' => 'nullable|numeric|min:0',
            'esic_deduction' => 'nullable|numeric|min:0',
            'professional_tax' => 'nullable|numeric|min:0',
            'tds' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'auto_statutory' => 'nullable|boolean',
            'effective_from' => 'sometimes|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'status' => 'nullable|string',
        ]);

        $salaryStructure->update($validated);
        $this->syncStatutory($salaryStructure);

        return response()->json($salaryStructure->fresh()->load('employee'));
    }

    /**
     * When a structure uses automatic statutory deductions, keep the stored PF
     * and ESIC columns in step with the rule so lists, reports and the payslip
     * show the same numbers the pay run will charge. Manual structures are left
     * exactly as entered.
     */
    protected function syncStatutory(SalaryStructure $structure): void
    {
        if (!$structure->auto_statutory) {
            return;
        }

        $statutory = StatutoryDeductions::forStructure($structure);

        $structure->forceFill([
            'pf_deduction' => $statutory['pf'],
            'esic_deduction' => $statutory['esic'],
        ])->save();
    }

    public function destroy(SalaryStructure $salaryStructure)
    {
        $this->authorize('payroll.manage');
        $salaryStructure->delete();
        return response()->noContent();
    }
}
