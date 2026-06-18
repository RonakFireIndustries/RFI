<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => 'nullable|exists:sites,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'device_info' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'GPS coordinates are required. Enable location services to check in.',
            'longitude.required' => 'GPS coordinates are required. Enable location services to check in.',
            'latitude.between' => 'Invalid GPS coordinates detected.',
            'longitude.between' => 'Invalid GPS coordinates detected.',
        ];
    }
}
