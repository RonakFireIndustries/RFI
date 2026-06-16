<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::all();
    }

    public function store(Request $request)
    {
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
}
