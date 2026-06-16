<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('manage', \App\Models\Payroll::class);
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'month_year' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'status' => 'nullable|in:Pending,Processed,Paid',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee is required',
            'month_year.required' => 'Month and year are required',
            'basic_salary.required' => 'Basic salary is required',
            'net_salary.required' => 'Net salary is required',
        ];
    }
}
