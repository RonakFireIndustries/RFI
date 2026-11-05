<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $this->authorize('payments.view');

        return Payment::with(['supplierLedger:id,name', 'customerLedger:id,name'])->get();
    }

    public function store(Request $request)
    {
        $this->authorize('payments.create');

        $validated = $request->validate([
            'type' => 'required|in:Payable,Receivable',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'supplier_ledger_id' => 'nullable|exists:ledger_accounts,id',
            'customer_ledger_id' => 'nullable|exists:ledger_accounts,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
        ]);

        $validated['created_by'] = Auth::id();

        $payment = Payment::create($validated);
        return response()->json($payment, 201);
    }

    public function show(Payment $payment)
    {
        $this->authorize('payments.view');

        return $payment->load(['supplierLedger:id,name', 'customerLedger:id,name']);
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorize('payments.create');

        $validated = $request->validate([
            'type' => 'sometimes|in:Payable,Receivable',
            'amount' => 'sometimes|numeric|min:0',
            'payment_date' => 'sometimes|date',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'supplier_ledger_id' => 'nullable|exists:ledger_accounts,id',
            'customer_ledger_id' => 'nullable|exists:ledger_accounts,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
        ]);

        $payment->update($validated);
        return $payment->load(['supplierLedger:id,name', 'customerLedger:id,name']);
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('payments.create');

        $payment->delete();
        return response()->noContent();
    }
}
