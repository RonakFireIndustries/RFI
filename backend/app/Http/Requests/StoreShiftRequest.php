<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:shifts,name',
            'start_time' => 'required|string', // Support time strings
            'end_time' => 'required|string',
            'grace_period' => 'nullable|integer|min:0',
            'late_threshold' => 'nullable|integer|min:0',
            'half_day_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ];
    }
}
