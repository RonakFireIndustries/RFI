<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'sometimes|exists:employees,id',
            'site_id' => 'nullable|exists:sites,id',
            'date' => 'required|date',
            'work_description' => 'required|string',
            'tasks_completed' => 'nullable|string',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'issues_faced' => 'nullable|string',
            'materials_used' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'nullable|in:Draft,Submitted',
        ];
    }
}
