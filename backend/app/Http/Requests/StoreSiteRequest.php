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
            'code' => 'nullable|string|max:50',
            'client_details' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'allowed_radius' => 'nullable|integer|min:1|max:10000',
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
