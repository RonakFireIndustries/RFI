<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $this->authorize('view_payments');

        return Payment::all();
    }

    public function store(Request $request)
    {
        $this->authorize('create_payments');

        $validated = $request->validate([
            'type' => 'required|in:Payable,Receivable',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
        ]);

        $validated['created_by'] = Auth::id();

        $payment = Payment::create($validated);
        return response()->json($payment, 201);
    }

    public function show(Payment $payment)
    {
        $this->authorize('view_payments');

        return $payment;
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorize('create_payments');

        $validated = $request->validate([
            'type' => 'sometimes|in:Payable,Receivable',
            'amount' => 'sometimes|numeric|min:0',
            'payment_date' => 'sometimes|date',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
        ]);

        $payment->update($validated);
        return $payment;
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('create_payments');

        $payment->delete();
        return response()->noContent();
    }
}
