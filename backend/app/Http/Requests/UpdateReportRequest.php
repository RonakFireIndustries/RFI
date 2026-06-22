<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit_reports');
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:report_categories,id',
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:reports,slug,' . $this->route('report'),
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'api_endpoint' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive,draft',
            'parameters' => 'nullable|json',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
