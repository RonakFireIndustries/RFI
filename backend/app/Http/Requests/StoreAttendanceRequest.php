<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Attendance::class);
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
            'status' => 'required|string',
            'site_id' => 'nullable|exists:sites,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'remarks' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee is required',
            'date.required' => 'Date is required',
            'status.required' => 'Status is required',
        ];
    }
}
