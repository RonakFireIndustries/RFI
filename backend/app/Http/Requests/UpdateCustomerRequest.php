<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('customer'));
    }

    public function rules()
    {
        $customerId = $this->route('customer')->id;
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:customers,email,' . $customerId,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'status' => 'nullable|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Email already exists',
        ];
    }
}
