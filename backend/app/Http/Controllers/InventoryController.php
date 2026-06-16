<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Http\Resources\InventoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['product.category', 'product.supplier', 'warehouse', 'branch']);
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        return InventoryResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required_without:warehouse_id|exists:branches,id',
            'warehouse_id' => 'required_without:branch_id|exists:branches,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $validated['branch_id'] = $validated['branch_id'] ?? $validated['warehouse_id'];

        $inventory = Inventory::updateOrCreate(
            ['product_id' => $validated['product_id'], 'branch_id' => $validated['branch_id']],
            ['quantity' => $validated['quantity']]
        );

        return new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse', 'branch', 'transactions']));
    }

    public function show(Inventory $inventory)
    {
        return new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse', 'branch', 'transactions']));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'warehouse_id' => 'sometimes|exists:branches,id',
            'quantity' => 'sometimes|integer|min:0',
        ]);

        if (isset($validated['warehouse_id'])) {
            $validated['branch_id'] = $validated['warehouse_id'];
            unset($validated['warehouse_id']);
        }

        $inventory->update($validated);

        return new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse', 'branch', 'transactions']));
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->noContent();
    }

    public function transaction(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'type' => 'required|string|in:purchase,sale,return,damage,transfer_in,transfer_out,adjustment',
            'quantity' => 'required|integer',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $validated['product_id'], 'branch_id' => $validated['branch_id']],
                ['quantity' => 0]
            );

            $quantity = $validated['quantity'];
            $type = $validated['type'];

            $isAddition = in_array($type, ['purchase', 'return', 'transfer_in']);
            $isSubtraction = in_array($type, ['sale', 'damage', 'transfer_out']);
            
            if ($isSubtraction && $inventory->quantity < abs($quantity)) {
                return response()->json(['message' => 'Insufficient stock for this transaction.'], 400);
            }

            if ($isAddition) {
                $inventory->quantity += abs($quantity);
            } elseif ($isSubtraction) {
                $inventory->quantity -= abs($quantity);
            } else {
                $inventory->quantity += $quantity;
            }

            $inventory->save();

            $transaction = InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'user_id' => optional($request->user())->id,
                'type' => $type,
                'quantity' => $quantity,
                'reference_type' => $validated['reference_type'] ?? null,
                'reference_id' => $validated['reference_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Transaction recorded successfully',
                'inventory' => new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse', 'branch'])),
                'transaction' => $transaction
            ]);
        });
    }

    public function transactions(Request $request)
    {
        $query = InventoryTransaction::with(['inventory.product', 'inventory.branch', 'user']);
        
        if ($request->has('branch_id')) {
            $query->whereHas('inventory', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }
        
        return $query->latest()->get();
    }
}
