<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Leave::class);
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'leave_type' => 'required|string',
            'reason' => 'nullable|string',
            'status' => 'nullable|in:Pending,Approved,Rejected',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee is required',
            'from_date.required' => 'From date is required',
            'to_date.required' => 'To date is required',
            'leave_type.required' => 'Leave type is required',
        ];
    }
}
