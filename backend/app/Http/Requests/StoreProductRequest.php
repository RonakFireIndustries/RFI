<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:products,name',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'status' => 'nullable|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Product name is required',
            'name.unique' => 'Product name must be unique',
            'price.required' => 'Price is required',
        ];
    }
}
