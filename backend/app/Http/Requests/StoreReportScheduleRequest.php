<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('schedule_reports');
    }

    public function rules(): array
    {
        return [
            'report_id' => 'required|exists:reports,id',
            'name' => 'required|string|max:255',
            'frequency' => 'required|string|in:daily,weekly,monthly,quarterly,yearly,custom',
            'config' => 'nullable|json',
            'next_run_at' => 'nullable|date',
            'recipients' => 'nullable|string',
            'format' => 'nullable|string|in:pdf,csv,xlsx',
        ];
    }
}
