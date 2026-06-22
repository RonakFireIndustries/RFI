<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['customer', 'creator'])->orderBy('created_at', 'desc')->get();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'status' => 'required|string',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            // Generate Invoice Number
            $customer = Customer::find($validated['customer_id']);
            
            $customerInitials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $customer->name), 0, 2));
            
            // Get max sequence for this customer
            $lastInvoice = Invoice::where('customer_id', $customer->id)
                                  ->orderBy('id', 'desc')
                                  ->first();
            
            $seq = 1;
            if ($lastInvoice && preg_match('/-(\d+)$/', $lastInvoice->invoice_number, $matches)) {
                $seq = intval($matches[1]) + 1;
            }
            
            $invoiceNumber = "RFI-{$customerInitials}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Calculate Totals (header-level GST)
            $subtotal = collect($validated['items'])->sum(function($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $gstRate = $validated['gst_rate'] ?? 0;
            $taxAmount = $subtotal * ($gstRate / 100);

            $gstType = $validated['gst_type'] ?? 'cgst';
            $cgst_total = 0;
            $sgst_total = 0;
            $igst_total = 0;

            if ($gstType === 'igst') {
                $igst_total = $taxAmount;
            } else {
                $cgst_total = $taxAmount / 2;
                $sgst_total = $taxAmount / 2;
            }
            
            $grand_total = $subtotal + $taxAmount;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'status' => $validated['status'],
                'due_date' => $validated['due_date'] ?? null,
                'issue_date' => now()->toDateString(),
                'total_amount' => $grand_total,
                'notes' => $validated['notes'],
                'terms' => $validated['terms'],
                'subtotal' => $subtotal,
                'cgst_total' => $cgst_total,
                'sgst_total' => $sgst_total,
                'igst_total' => $igst_total,
                'grand_total' => $grand_total,
                'gst_type' => $gstType,
                'gst_rate' => $gstRate,
                'created_by' => Auth::id()
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_description' => $item['item_description'],
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            DB::commit();

            return response()->json($invoice->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating invoice: ' . $e->getMessage()], 500);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'creator']);
        return response()->json($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'payment_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0'
        ]);

        $invoice->update($validated);

        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(null, 204);
    }

    public function generatePDF(Invoice $invoice, \App\Services\InvoicePdfService $pdfService)
    {
        $pdf = $pdfService->generate($invoice);
        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }
}
