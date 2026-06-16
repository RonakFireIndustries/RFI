<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeSiteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\EmployeeSite::class);
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'site_id' => 'required|exists:sites,id',
            'assigned_at' => 'nullable|date',
            'role' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee is required',
            'employee_id.exists' => 'Selected employee does not exist',
            'site_id.required' => 'Site is required',
            'site_id.exists' => 'Selected site does not exist',
        ];
    }
}
