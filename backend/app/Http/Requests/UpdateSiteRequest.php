<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $siteId = $this->route('site')->id ?? $this->route('site');
        
        return [
            'name' => 'required|string|unique:sites,name,' . $siteId,
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
            'geo_fencing_enabled' => 'boolean',
            'status' => 'required|in:Active,Inactive',
            'site_manager_id' => 'nullable|exists:employees,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
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
