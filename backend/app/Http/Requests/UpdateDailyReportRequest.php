<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyReportRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('daily_report'));
    }

    public function rules()
    {
        return [
            'employee_id' => 'sometimes|exists:employees,id',
            'site_id' => 'nullable|exists:sites,id',
            'report_date' => 'sometimes|date',
            'content' => 'sometimes|string',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.exists' => 'Selected employee does not exist',
            'report_date.date' => 'Report date must be a valid date',
        ];
    }
}
