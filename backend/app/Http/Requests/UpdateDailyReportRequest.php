<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => 'nullable|exists:sites,id',
            'date' => 'sometimes|date',
            'work_description' => 'sometimes|string',
            'tasks_completed' => 'nullable|string',
            'hours_worked' => 'sometimes|numeric|min:0|max:24',
            'issues_faced' => 'nullable|string',
            'materials_used' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'status' => 'nullable|in:Draft,Submitted',
        ];
    }
}
