<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'location_type' => 'required|string|in:App\Models\Site',
            'location_id' => 'required|integer',
            'transaction_type' => 'required|in:opening_stock,purchase,purchase_return,issue,consumption,transfer_in,transfer_out,adjustment,damage,sales,sales_return',
            'quantity' => 'required|numeric',
            'unit_price' => 'nullable|numeric|min:0',
            'to_location_type' => 'nullable|string|in:App\Models\Site',
            'to_location_id' => 'nullable|integer',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();
            if (($data['transaction_type'] ?? '') === 'transfer_out') {
                if (empty($data['to_location_type']) || empty($data['to_location_id'])) {
                    $validator->errors()->add('to_location_id', 'Destination location is required for transfers.');
                }
            }
        });
    }
}
