<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    /**
     * Add stock to inventory (e.g. from GRN or Sales Return)
     */
    public function addStock($productId, $branchId, $quantity, $referenceType, $referenceId, $notes = '')
    {
        // Must use withoutGlobalScopes to bypass branch scoping if creating for a different branch
        $inventory = Inventory::withoutGlobalScopes()->firstOrCreate(
            ['product_id' => $productId, 'branch_id' => $branchId],
            ['quantity' => 0]
        );

        $inventory->increment('quantity', $quantity);

        InventoryTransaction::withoutGlobalScopes()->create([
            'inventory_id' => $inventory->id,
            'branch_id' => $branchId, // Ensure branch_id is explicitly set
            'user_id' => Auth::id(),
            'type' => 'in',
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes
        ]);

        return $inventory;
    }

    /**
     * Remove stock from inventory (e.g. from Delivery Note or Purchase Return)
     */
    public function removeStock($productId, $branchId, $quantity, $referenceType, $referenceId, $notes = '')
    {
        $inventory = Inventory::withoutGlobalScopes()->firstOrCreate(
            ['product_id' => $productId, 'branch_id' => $branchId],
            ['quantity' => 0]
        );

        if ($inventory->quantity < $quantity) {
            throw new \Exception("Insufficient stock for product ID {$productId}");
        }

        $inventory->decrement('quantity', $quantity);

        InventoryTransaction::withoutGlobalScopes()->create([
            'inventory_id' => $inventory->id,
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
            'type' => 'out',
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes
        ]);

        return $inventory;
    }
}
