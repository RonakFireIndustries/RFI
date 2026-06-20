<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;

class SalesOrderController extends Controller
{
    public function index()
    {
        return SalesOrder::with('items')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $subtotal = collect($request->items)->sum(function($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $gstRate = $request->input('gst_rate', 0);
        $taxAmount = $subtotal * ($gstRate / 100);

        $so = SalesOrder::create([
            'so_number' => 'SO-' . time(),
            'customer_id' => $request->customer_id,
            'status' => $request->input('status', 'Pending Approval'),
            'created_by' => Auth::id(),
            'notes' => $request->notes,
            'total_amount' => $subtotal,
            'tax_amount' => $taxAmount,
            'gst_type' => $request->gst_type,
            'gst_rate' => $gstRate,
        ]);

        foreach ($request->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $so->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json($so->load('items'), 201);
    }

    public function approve(Request $request, $id)
    {
        $so = SalesOrder::findOrFail($id);
        
        $so->status = 'Approved';
        $so->approved_by = Auth::id();
        $so->save();

        return response()->json($so);
    }

    public function show($id)
    {
        return SalesOrder::with('items')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $so = SalesOrder::findOrFail($id);

        if ($so->status !== 'Pending Approval') {
            return response()->json(['message' => 'Cannot update an approved order'], 400);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $subtotal = collect($request->items)->sum(function($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $gstRate = $request->input('gst_rate', 0);
        $taxAmount = $subtotal * ($gstRate / 100);

        $so->update([
            'customer_id' => $request->customer_id,
            'notes' => $request->notes,
            'total_amount' => $subtotal,
            'tax_amount' => $taxAmount,
            'gst_type' => $request->gst_type,
            'gst_rate' => $gstRate,
        ]);

        // Recreate items
        $so->items()->delete();
        foreach ($request->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $so->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json($so->load('items'));
    }

    public function destroy($id)
    {
        $so = SalesOrder::findOrFail($id);
        $so->items()->delete();
        $so->delete();
        return response()->noContent();
    }
}
