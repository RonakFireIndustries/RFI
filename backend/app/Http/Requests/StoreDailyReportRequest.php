<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyReportRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\DailyReport::class);
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'site_id' => 'nullable|exists:sites,id',
            'report_date' => 'required|date',
            'content' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee is required',
            'report_date.required' => 'Report date is required',
            'content.required' => 'Report content is required',
        ];
    }
}
