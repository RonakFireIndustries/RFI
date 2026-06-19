<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255|unique:shifts,name,' . $this->route('shift')->id,
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'grace_period' => 'nullable|integer|min:0',
            'late_threshold' => 'nullable|integer|min:0',
            'half_day_threshold' => 'nullable|integer|min:0',
            'status' => 'nullable|in:Active,Inactive',
        ];
    }
}
