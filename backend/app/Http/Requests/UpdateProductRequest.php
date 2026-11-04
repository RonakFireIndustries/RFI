<?php

namespace App\Http\Requests;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'sku' => ['sometimes', 'string', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'product_code' => 'nullable|string|max:100',
            'name' => 'sometimes|string|max:255',
            'hsn_code' => 'nullable|string|max:255',
            'dimension' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => ['sometimes', 'required', 'exists:units,id'],
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'opening_stock' => 'nullable|numeric|min:0',
            'opening_stock_unit_id' => 'nullable|exists:units,id',
            // A product's real stock lives in product_stock rows, one per
            // site. When opening stock is entered for a product that has never
            // moved, the site is what turns that figure into an actual stock
            // row; without it the number was saved and "Stock" still read 0.
            'site_id' => [
                Rule::requiredIf(function () {
                    if ((float) $this->input('opening_stock', 0) <= 0) {
                        return false;
                    }

                    $product = $this->route('product');

                    if (! $product instanceof Product) {
                        return false;
                    }

                    return ! $product->stock()->exists() && ! $product->transactions()->exists();
                }),
                'nullable',
                'exists:sites,id',
            ],
            'status' => 'nullable|string|in:active,inactive',
        ];

        if (ProductResource::canManageSalesPrice($this)) {
            $rules['selling_price'] = 'sometimes|numeric|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'site_id.required' => 'Select the site that will hold this opening stock, otherwise no stock is recorded.',
        ];
    }
}
