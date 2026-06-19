<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'code' => ['sometimes', 'string', 'max:20', Rule::unique('units', 'code')->ignore($this->route('unit'))],
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
