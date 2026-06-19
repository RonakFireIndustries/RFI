<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'from_location_type' => 'required|string|in:App\Models\Branch,App\Models\Site',
            'from_location_id' => 'required|integer',
            'to_location_type' => 'required|string|in:App\Models\Branch,App\Models\Site',
            'to_location_id' => 'required|integer|different:from_location_id',
            'quantity' => 'required|numeric|gt:0',
            'notes' => 'nullable|string',
        ];
    }
}
