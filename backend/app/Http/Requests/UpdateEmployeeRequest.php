<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->route('employee')->user_id,
            'branch_id' => 'sometimes|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'salary' => 'nullable|numeric|min:0',
            'shift' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'status' => 'nullable|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Email already exists',
        ];
    }
}
