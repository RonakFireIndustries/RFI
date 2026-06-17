<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => 'sometimes|exists:leave_types,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'is_half_day' => 'boolean',
            'reason' => 'sometimes|string',
            'attachment_path' => 'nullable|string',
            'status' => 'nullable|in:Draft,Submitted',
        ];
    }
}
