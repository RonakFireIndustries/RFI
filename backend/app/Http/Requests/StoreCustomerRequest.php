<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Customer::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'nullable|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Customer name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'branch_id.required' => 'Branch is required',
        ];
    }
}
