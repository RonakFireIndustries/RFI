<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('department'));
    }

    public function rules()
    {
        $deptId = $this->route('department')->id;
        
        return [
            'name' => 'required|string|unique:departments,name,' . $deptId,
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
        ];
    }
}
