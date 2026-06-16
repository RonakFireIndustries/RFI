<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Invoice::class);
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:Draft,Sent,Paid,Cancelled',
            'notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'customer_id.required' => 'Customer is required',
            'invoice_date.required' => 'Invoice date is required',
            'items.required' => 'At least one invoice item is required',
        ];
    }
}
