<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Payment::class);
    }

    public function rules()
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Check,Bank Transfer,Credit Card,Online',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:Pending,Completed,Failed,Refunded',
        ];
    }

    public function messages()
    {
        return [
            'invoice_id.required' => 'Invoice is required',
            'amount.required' => 'Payment amount is required',
            'payment_date.required' => 'Payment date is required',
            'payment_method.required' => 'Payment method is required',
        ];
    }
}
