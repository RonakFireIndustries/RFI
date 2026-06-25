<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\ProductStock;

class SalesOrderController extends Controller
{
    public function index()
    {
        $this->authorize('view_sales_orders');

        return SalesOrder::with('items')->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create_sales_orders');

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.custom_product_name' => 'nullable|string|max:255|required_without:items.*.product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.hsn_code' => 'nullable|string|max:20',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        $so = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $taxAmount = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                $itemGstRate = $item['gst_rate'] ?? 0;
                $taxAmount += $lineTotal * ($itemGstRate / 100);
            }

            $so = SalesOrder::create([
                'so_number' => 'SO-' . time(),
                'customer_id' => $request->customer_id,
                'status' => 'Pending',
                'created_by' => Auth::id(),
                'notes' => $request->notes,
                'total_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'gst_type' => $request->gst_type,
            ]);

            foreach ($request->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id' => $item['product_id'] ?: null,
                    'custom_product_name' => $item['custom_product_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'gst_rate' => $item['gst_rate'] ?? 0,
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $so;
        });

        return response()->json($so->load('items'), 201);
    }

    public function show($id)
    {
        $this->authorize('view_sales_orders');

        return SalesOrder::with(['items.product', 'customer'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('create_sales_orders');

        $so = SalesOrder::findOrFail($id);

        if ($so->status !== 'Pending') {
            return response()->json(['message' => 'Cannot update a non-pending order'], 400);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.custom_product_name' => 'nullable|string|max:255|required_without:items.*.product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.hsn_code' => 'nullable|string|max:20',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $so) {
            $subtotal = 0;
            $taxAmount = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                $itemGstRate = $item['gst_rate'] ?? 0;
                $taxAmount += $lineTotal * ($itemGstRate / 100);
            }

            $so->update([
                'customer_id' => $request->customer_id,
                'notes' => $request->notes,
                'total_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'gst_type' => $request->gst_type,
            ]);

            $so->items()->delete();
            foreach ($request->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id' => $item['product_id'] ?: null,
                    'custom_product_name' => $item['custom_product_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'gst_rate' => $item['gst_rate'] ?? 0,
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return response()->json($so->load('items'));
    }

    public function approve(Request $request, $id)
    {
        $this->authorize('view_sales_orders');

        $so = SalesOrder::findOrFail($id);

        if ($so->status !== 'Pending Approval' && $so->status !== 'Pending') {
            return response()->json(['message' => 'Order cannot be approved in current status'], 400);
        }

        $so->status = 'Approved';
        $so->approved_by = Auth::id();
        $so->save();

        return response()->json($so);
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('view_sales_orders');

        $so = SalesOrder::findOrFail($id);

        if ($so->status !== 'Pending Approval' && $so->status !== 'Pending') {
            return response()->json(['message' => 'Order cannot be rejected in current status'], 400);
        }

        $so->status = 'Rejected';
        $so->save();

        return response()->json($so);
    }

    /**
     * Store Manager confirms delivery of goods → inventory is updated.
     */
    public function confirmDelivery(Request $request, $id)
    {
        $this->authorize('manage_inventory');

        $so = SalesOrder::with('items')->findOrFail($id);

        if ($so->status !== 'Approved' && $so->status !== 'Pending') {
            return response()->json(['message' => 'Order cannot be delivered in current status'], 400);
        }

        DB::transaction(function () use ($so) {
            foreach ($so->items as $item) {
                $stock = ProductStock::firstOrNew([
                    'product_id' => $item->product_id,
                    'location_type' => 'site',
                    'location_id' => $so->site_id ?? 1,
                ]);

                $currentQty = $stock->quantity ?? 0;
                if ($currentQty < $item->quantity) {
                    throw new \Exception("Insufficient stock for product ID {$item->product_id}");
                }

                $stock->quantity = $currentQty - $item->quantity;
                $stock->available_quantity = ($stock->available_quantity ?? 0) - $item->quantity;
                $stock->save();
            }

            $so->status = 'Delivered';
            $so->delivered_by = Auth::id();
            $so->delivered_at = now();
            $so->save();
        });

        return response()->json($so->load('items'));
    }

    public function destroy($id)
    {
        $this->authorize('manage_inventory');

        $so = SalesOrder::findOrFail($id);
        $so->items()->delete();
        $so->delete();
        return response()->noContent();
    }
}
