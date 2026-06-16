<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('manage', $this->route('payroll'));
    }

    public function rules()
    {
        return [
            'employee_id' => 'sometimes|exists:employees,id',
            'month_year' => 'sometimes|string',
            'basic_salary' => 'sometimes|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'net_salary' => 'sometimes|numeric|min:0',
            'status' => 'nullable|in:Pending,Processed,Paid',
        ];
    }

    public function messages()
    {
        return [
            'basic_salary.numeric' => 'Basic salary must be a number',
            'net_salary.numeric' => 'Net salary must be a number',
        ];
    }
}
