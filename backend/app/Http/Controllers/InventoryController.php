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
        $this->authorize('view_inventory');

        $query = Inventory::with(['product.category', 'product.supplier', 'warehouse']);
        return InventoryResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $inventory = Inventory::updateOrCreate(
            ['product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity']]
        );

        return new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse', 'transactions']));
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view_inventory');

        return new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse', 'transactions']));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:0',
        ]);

        $inventory->update($validated);

        return new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse', 'transactions']));
    }

    public function destroy(Inventory $inventory)
    {
        $this->authorize('manage_inventory');

        $inventory->delete();

        return response()->noContent();
    }

    public function transaction(Request $request)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|string|in:purchase,sale,return,damage,transfer_in,transfer_out,adjustment',
            'quantity' => 'required|integer',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $validated['product_id']],
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
                'inventory' => new InventoryResource($inventory->load(['product.category', 'product.supplier', 'warehouse'])),
                'transaction' => $transaction
            ]);
        });
    }

    public function transactions(Request $request)
    {
        $this->authorize('view_inventory');

        $query = InventoryTransaction::with(['inventory.product', 'user']);
        
        return $query->latest()->get();
    }
}
