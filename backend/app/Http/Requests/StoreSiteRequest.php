<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Site::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:sites,name',
            'client_details' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'site_manager_id' => 'nullable|exists:employees,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Site name is required',
            'name.unique' => 'Site name must be unique',
            'status.required' => 'Status is required',
            'site_manager_id.exists' => 'Selected site manager does not exist',
        ];
    }
}
