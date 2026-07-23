<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{
    public function index()
    {
        $this->authorize('manage_payroll');
        return response()->json(SalaryStructure::with('employee')->get());
    }

    public function store(Request $request)
    {
        $this->authorize('manage_payroll');
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
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'status' => 'nullable|string',
        ]);

        $structure = SalaryStructure::create($validated);
        return response()->json($structure->load('employee'), 201);
    }

    public function show(SalaryStructure $salaryStructure)
    {
        return response()->json($salaryStructure->load('employee'));
    }

    public function update(Request $request, SalaryStructure $salaryStructure)
    {
        $this->authorize('manage_payroll');
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
            'effective_from' => 'sometimes|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'status' => 'nullable|string',
        ]);

        $salaryStructure->update($validated);
        return response()->json($salaryStructure->load('employee'));
    }

    public function destroy(SalaryStructure $salaryStructure)
    {
        $this->authorize('manage_payroll');
        $salaryStructure->delete();
        return response()->noContent();
    }
}
