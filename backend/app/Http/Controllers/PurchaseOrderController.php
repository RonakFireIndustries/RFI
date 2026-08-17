<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductStock;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $this->authorize('view_purchase_orders');

        return PurchaseOrder::with('items')->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create_purchase_orders');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'site_id' => 'nullable|exists:sites,id',
            'items' => 'required|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.custom_product_name' => 'nullable|string|max:255|required_without:items.*.product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.hsn_code' => 'nullable|string|max:20',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'shipping_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'other_cost_note' => 'nullable|string|max:255',
            'terms_conditions' => 'nullable|string',
        ]);

        $po = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $taxAmount = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_cost'];
                $subtotal += $lineTotal;
                $itemGstRate = $item['gst_rate'] ?? 0;
                $taxAmount += $lineTotal * ($itemGstRate / 100);
            }

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . time(),
                'supplier_id' => $request->supplier_id,
                'site_id' => $request->site_id ?: null,
                'status' => 'Pending',
                'requested_by' => Auth::id(),
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'total_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'other_cost' => $request->other_cost ?? 0,
                'other_cost_note' => $request->other_cost_note ?? null,
                'gst_type' => $request->gst_type,
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'] ?: null,
                    'custom_product_name' => $item['custom_product_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'gst_rate' => $item['gst_rate'] ?? 0,
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'total' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            return $po;
        });

        return response()->json($po->load('items'), 201);
    }

    public function approve(Request $request, $id)
    {
        $this->authorize('view_purchase_orders');

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'Pending Approval' && $po->status !== 'Pending') {
            return response()->json(['message' => 'Order cannot be approved in current status'], 400);
        }

        $po->status = 'Approved';
        $po->approved_by = Auth::id();
        $po->save();

        return response()->json($po);
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('view_purchase_orders');

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'Pending Approval' && $po->status !== 'Pending') {
            return response()->json(['message' => 'Order cannot be rejected in current status'], 400);
        }

        $po->status = 'Rejected';
        $po->save();

        return response()->json($po);
    }

    /**
     * Store Manager confirms receipt of goods → inventory is updated.
     * Accepts partial receipt per line item via `items` payload: [{ id, received_qty }].
     */
    public function confirmReceipt(Request $request, $id)
    {
        $this->authorize('manage_inventory');

        $po = PurchaseOrder::with('items')->findOrFail($id);

        if ($po->status !== 'Approved' && $po->status !== 'Pending' && $po->status !== 'Partially Received') {
            return response()->json(['message' => 'Order cannot be received in current status'], 400);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.received_qty' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($po, $request) {
                $receivedById = collect($request->items)->keyBy('id');
                $allFullyReceived = true;

                foreach ($po->items as $item) {
                    $entry = $receivedById->get($item->id);
                    $remaining = $item->quantity - ($item->received_quantity ?? 0);
                    $receivedNow = $entry ? (int) $entry['received_qty'] : $remaining;
                    $itemLabel = $item->product?->name ?? $item->custom_product_name ?? "Item #{$item->id}";

                    if ($receivedNow < 0 || $receivedNow > $remaining) {
                        throw new \Exception("Received quantity for item '{$itemLabel}' exceeds ordered quantity");
                    }

                    if ($receivedNow > 0 && !is_null($item->product_id)) {
                        $stock = ProductStock::firstOrNew([
                            'product_id' => $item->product_id,
                            'location_type' => \App\Models\Site::class,
                            'location_id' => $po->site_id ?? 1,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $receivedNow;
                        $stock->available_quantity = ($stock->available_quantity ?? 0) + $receivedNow;
                        $stock->save();
                    }

                    $item->received_quantity = ($item->received_quantity ?? 0) + $receivedNow;
                    $item->save();

                    if (($item->received_quantity ?? 0) < $item->quantity) {
                        $allFullyReceived = false;
                    }
                }

                $po->status = $allFullyReceived ? 'Received' : 'Partially Received';
                $po->received_by = Auth::id();
                $po->received_at = now();
                $po->save();
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json($po->load('items'));
    }

    public function show($id)
    {
        $this->authorize('view_purchase_orders');

        return PurchaseOrder::with(['items.product', 'supplier', 'site', 'payments'])
            ->withSum('payments as paid_amount', 'amount')
            ->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('create_purchase_orders');

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'Pending') {
            return response()->json(['message' => 'Cannot update a non-pending order'], 400);
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'site_id' => 'nullable|exists:sites,id',
            'items' => 'required|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.custom_product_name' => 'nullable|string|max:255|required_without:items.*.product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.hsn_code' => 'nullable|string|max:20',
            'gst_type' => 'nullable|string|in:cgst,sgst,igst',
            'shipping_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'other_cost_note' => 'nullable|string|max:255',
            'terms_conditions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $po) {
            $subtotal = 0;
            $taxAmount = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_cost'];
                $subtotal += $lineTotal;
                $itemGstRate = $item['gst_rate'] ?? 0;
                $taxAmount += $lineTotal * ($itemGstRate / 100);
            }

            $po->update([
                'supplier_id' => $request->supplier_id,
                'site_id' => $request->site_id ?: null,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'total_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'other_cost' => $request->other_cost ?? 0,
                'other_cost_note' => $request->other_cost_note ?? null,
                'gst_type' => $request->gst_type,
            ]);

            $po->items()->delete();
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'] ?: null,
                    'custom_product_name' => $item['custom_product_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'gst_rate' => $item['gst_rate'] ?? 0,
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'total' => $item['quantity'] * $item['unit_cost'],
                ]);
            }
        });

        return response()->json($po->load('items'));
    }

    public function destroy($id)
    {
        $this->authorize('manage_inventory');

        $po = PurchaseOrder::findOrFail($id);
        $po->items()->delete();
        $po->delete();
        return response()->noContent();
    }
}
