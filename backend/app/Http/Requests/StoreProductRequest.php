<?php

namespace App\Http\Requests;

use App\Http\Resources\ProductResource;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'sku' => 'nullable|string|unique:products,sku',
            'product_code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'opening_stock' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive',
            'site_id' => 'required_if:opening_stock,gt:0|nullable|exists:sites,id',
        ];

        if (ProductResource::canManageSalesPrice($this)) {
            $rules['purchase_price'] = 'required|numeric|min:0';
            $rules['selling_price'] = 'required|numeric|min:0';
        }

        return $rules;
    }
}
