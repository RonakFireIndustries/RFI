<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Department::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:departments,name',
            'description' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'head_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Department name is required',
            'name.unique' => 'Department name must be unique',
            'branch_id.required' => 'Branch is required',
        ];
    }
}
