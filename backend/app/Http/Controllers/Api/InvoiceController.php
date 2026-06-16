<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->header('X-Branch-ID');
        $query = Invoice::with(['customer', 'creator']);
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $invoices = $query->orderBy('created_at', 'desc')->get();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
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
            'items.*.tax_rate' => 'required|numeric|min:0',
            'items.*.is_igst' => 'boolean' // passed from frontend to know if IGST or CGST/SGST
        ]);

        DB::beginTransaction();

        try {
            // Generate Invoice Number
            $branch = Branch::find($validated['branch_id']);
            $customer = Customer::find($validated['customer_id']);
            
            $branchCode = strtoupper(substr($branch->name, 0, 3));
            $customerInitials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $customer->name), 0, 2));
            
            // Get max sequence for this branch & customer
            $lastInvoice = Invoice::where('branch_id', $branch->id)
                                  ->where('customer_id', $customer->id)
                                  ->orderBy('id', 'desc')
                                  ->first();
            
            $seq = 1;
            if ($lastInvoice && preg_match('/-(\d+)$/', $lastInvoice->invoice_number, $matches)) {
                $seq = intval($matches[1]) + 1;
            }
            
            $invoiceNumber = "RFI-{$branchCode}-{$customerInitials}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Calculate Totals
            $subtotal = 0;
            $cgst_total = 0;
            $sgst_total = 0;
            $igst_total = 0;
            
            $invoiceItems = [];
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                
                $taxAmount = $lineTotal * ($item['tax_rate'] / 100);
                $isIgst = $item['is_igst'] ?? false;
                
                $cgst = 0;
                $sgst = 0;
                $igst = 0;
                
                if ($isIgst) {
                    $igst = $taxAmount;
                    $igst_total += $igst;
                } else {
                    $cgst = $taxAmount / 2;
                    $sgst = $taxAmount / 2;
                    $cgst_total += $cgst;
                    $sgst_total += $sgst;
                }
                
                $finalLineTotal = $lineTotal + $taxAmount;
                
                $invoiceItems[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'item_description' => $item['item_description'],
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'cgst_amount' => $cgst,
                    'sgst_amount' => $sgst,
                    'igst_amount' => $igst,
                    'total' => $finalLineTotal
                ];
            }
            
            $grand_total = $subtotal + $cgst_total + $sgst_total + $igst_total;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'branch_id' => $validated['branch_id'],
                'customer_id' => $validated['customer_id'],
                'sales_order_id' => $validated['sales_order_id'],
                'status' => $validated['status'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'],
                'terms' => $validated['terms'],
                'subtotal' => $subtotal,
                'cgst_total' => $cgst_total,
                'sgst_total' => $sgst_total,
                'igst_total' => $igst_total,
                'grand_total' => $grand_total,
                'created_by' => Auth::id()
            ]);

            foreach ($invoiceItems as $itemData) {
                $invoice->items()->create($itemData);
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
        $invoice->load(['customer', 'branch', 'items.product', 'creator']);
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
