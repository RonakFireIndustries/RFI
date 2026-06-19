<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_unit_id' => 'sometimes|exists:units,id',
            'to_unit_id' => 'sometimes|exists:units,id|different:from_unit_id',
            'conversion_factor' => 'sometimes|numeric|gt:0',
        ];
    }
}
