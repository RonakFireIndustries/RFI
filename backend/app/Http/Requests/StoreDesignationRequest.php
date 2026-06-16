<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignationRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Designation::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:designations,name',
            'description' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Designation name is required',
            'name.unique' => 'Designation name must be unique',
            'branch_id.required' => 'Branch is required',
        ];
    }
}
