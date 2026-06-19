<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\ProductStock;
use App\Models\TransactionLedger;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function generateTransactionNumber(): string
    {
        $prefix = 'TXN-' . date('Ymd');
        $last = TransactionLedger::whereDate('created_at', today())
            ->lockForUpdate()
            ->count();
        return $prefix . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function generateRequestNumber(): string
    {
        $prefix = 'SRQ-' . date('Ymd');
        $last = \App\Models\StockRequest::whereDate('created_at', today())
            ->lockForUpdate()
            ->count();
        return $prefix . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function addStock($productId, $branchId, $quantity, $referenceType, $referenceId, $notes = '')
    {
        return $this->recordTransaction(
            productId: $productId,
            locationType: Branch::class,
            locationId: $branchId,
            transactionType: 'purchase',
            quantity: $quantity,
            toLocationType: null,
            toLocationId: null,
            referenceType: $referenceType,
            referenceId: $referenceId,
            notes: $notes
        );
    }

    public function removeStock($productId, $branchId, $quantity, $referenceType, $referenceId, $notes = '')
    {
        return $this->recordTransaction(
            productId: $productId,
            locationType: Branch::class,
            locationId: $branchId,
            transactionType: 'sales',
            quantity: $quantity,
            toLocationType: null,
            toLocationId: null,
            referenceType: $referenceType,
            referenceId: $referenceId,
            notes: $notes
        );
    }

    public function recordTransaction(
        int $productId,
        string $locationType,
        int $locationId,
        string $transactionType,
        float $quantity,
        float $unitPrice = 0,
        ?string $toLocationType = null,
        ?int $toLocationId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): TransactionLedger {
        return DB::transaction(function () use (
            $productId, $locationType, $locationId, $transactionType, $quantity,
            $unitPrice, $toLocationType, $toLocationId, $referenceType, $referenceId, $notes
        ) {
            $product = Product::findOrFail($productId);

            $stock = ProductStock::firstOrCreate(
                ['product_id' => $productId, 'location_type' => $locationType, 'location_id' => $locationId],
                ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0]
            );

            $quantityBefore = $stock->quantity;
            $quantityAfter = $quantityBefore;
            $absQty = abs($quantity);

            $isInflow = in_array($transactionType, [
                'opening_stock', 'purchase', 'purchase_return',
                'transfer_in', 'sales_return'
            ]);
            $isOutflow = in_array($transactionType, [
                'issue', 'consumption', 'transfer_out',
                'adjustment', 'damage', 'sales'
            ]);
            $isTransfer = $transactionType === 'transfer_out';

            if ($isTransfer && $toLocationType && $toLocationId) {
                if ($stock->quantity < $absQty) {
                    throw new \App\Exceptions\InsufficientStockException(
                        "Insufficient stock for transfer. Available: {$stock->quantity}, Required: {$absQty}"
                    );
                }
                $quantityAfter = $quantityBefore - $absQty;

                $destStock = ProductStock::firstOrCreate(
                    ['product_id' => $productId, 'location_type' => $toLocationType, 'location_id' => $toLocationId],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0]
                );
                $destStock->increment('quantity', $absQty);
                $destStock->update([
                    'available_quantity' => $destStock->quantity - $destStock->reserved_quantity
                ]);

                TransactionLedger::create([
                    'transaction_number' => $this->generateTransactionNumber(),
                    'product_id' => $productId,
                    'location_type' => $toLocationType,
                    'location_id' => $toLocationId,
                    'to_location_type' => null,
                    'to_location_id' => null,
                    'transaction_type' => 'transfer_in',
                    'quantity' => $absQty,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $absQty,
                    'quantity_before' => $destStock->quantity - $absQty,
                    'quantity_after' => $destStock->quantity,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'notes' => $notes,
                    'created_by' => Auth::id(),
                ]);
            } elseif ($isOutflow) {
                if ($stock->quantity < $absQty) {
                    throw new \App\Exceptions\InsufficientStockException(
                        "Insufficient stock. Available: {$stock->quantity}, Required: {$absQty}"
                    );
                }
                $quantityAfter = $quantityBefore - $absQty;
            } elseif ($isInflow) {
                $quantityAfter = $quantityBefore + $absQty;
            } else {
                $quantityAfter = $quantityBefore + $quantity;
            }

            $stock->update([
                'quantity' => $quantityAfter,
                'available_quantity' => $quantityAfter - $stock->reserved_quantity
            ]);

            return TransactionLedger::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'product_id' => $productId,
                'location_type' => $locationType,
                'location_id' => $locationId,
                'to_location_type' => $isTransfer ? $toLocationType : null,
                'to_location_id' => $isTransfer ? $toLocationId : null,
                'transaction_type' => $transactionType,
                'quantity' => $isOutflow || $isTransfer ? -$absQty : $absQty,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $absQty,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        });
    }
}
