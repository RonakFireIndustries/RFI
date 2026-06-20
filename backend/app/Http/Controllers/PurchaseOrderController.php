<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return PurchaseOrder::with('items')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $subtotal = collect($request->items)->sum(function($item) {
            return $item['quantity'] * $item['unit_cost'];
        });

        $gstRate = $request->input('gst_rate', 0);
        $taxAmount = $subtotal * ($gstRate / 100);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . time(),
            'supplier_id' => $request->supplier_id,
            'status' => $request->input('status', 'Pending Approval'),
            'requested_by' => Auth::id(),
            'notes' => $request->notes,
            'total_amount' => $subtotal,
            'tax_amount' => $taxAmount,
            'gst_type' => $request->gst_type,
            'gst_rate' => $gstRate,
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'total' => $item['quantity'] * $item['unit_cost'],
            ]);
        }

        return response()->json($po->load('items'), 201);
    }

    public function approve(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        
        $po->status = 'Approved';
        $po->approved_by = Auth::id();
        $po->save();

        return response()->json($po);
    }

    public function show($id)
    {
        return PurchaseOrder::with('items')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'Pending Approval') {
            return response()->json(['message' => 'Cannot update an approved order'], 400);
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $subtotal = collect($request->items)->sum(function($item) {
            return $item['quantity'] * $item['unit_cost'];
        });

        $gstRate = $request->input('gst_rate', 0);
        $taxAmount = $subtotal * ($gstRate / 100);

        $po->update([
            'supplier_id' => $request->supplier_id,
            'notes' => $request->notes,
            'total_amount' => $subtotal,
            'tax_amount' => $taxAmount,
            'gst_type' => $request->gst_type,
            'gst_rate' => $gstRate,
        ]);

        // Recreate items
        $po->items()->delete();
        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'total' => $item['quantity'] * $item['unit_cost'],
            ]);
        }

        return response()->json($po->load('items'));
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->items()->delete();
        $po->delete();
        return response()->noContent();
    }
}
