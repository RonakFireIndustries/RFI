<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules()
    {
        $productId = $this->route('product')->id;
        
        return [
            'name' => 'sometimes|string|unique:products,name,' . $productId,
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $productId,
            'status' => 'nullable|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Product name must be unique',
        ];
    }
}
