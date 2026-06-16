<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Employee::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'salary' => 'nullable|numeric|min:0',
            'shift' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'role' => 'nullable|string|exists:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
            'status' => 'nullable|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Employee name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'branch_id.required' => 'Branch is required',
        ];
    }
}
