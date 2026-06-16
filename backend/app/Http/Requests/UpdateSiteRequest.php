<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('site'));
    }

    public function rules()
    {
        $siteId = $this->route('site')->id ?? $this->route('site');
        
        return [
            'name' => 'required|string|unique:sites,name,' . $siteId,
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
        ];
    }
}
