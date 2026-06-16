<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('designation'));
    }

    public function rules()
    {
        $desgId = $this->route('designation')->id;
        
        return [
            'name' => 'required|string|unique:designations,name,' . $desgId,
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
        ];
    }
}
