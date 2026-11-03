<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\TransactionLedger;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private UnitConversionService $unitConversion
    ) {}

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

    public function addStock($productId, $locationType, $locationId, $quantity, $referenceType, $referenceId, $notes = '', ?int $unitId = null)
    {
        return $this->recordTransaction(
            productId: $productId,
            locationType: $locationType,
            locationId: $locationId,
            transactionType: 'purchase',
            quantity: $quantity,
            toLocationType: null,
            toLocationId: null,
            referenceType: $referenceType,
            referenceId: $referenceId,
            notes: $notes,
            unitId: $unitId
        );
    }

    public function removeStock($productId, $locationType, $locationId, $quantity, $referenceType, $referenceId, $notes = '', ?int $unitId = null)
    {
        return $this->recordTransaction(
            productId: $productId,
            locationType: $locationType,
            locationId: $locationId,
            transactionType: 'sales',
            quantity: $quantity,
            toLocationType: null,
            toLocationId: null,
            referenceType: $referenceType,
            referenceId: $referenceId,
            notes: $notes,
            unitId: $unitId
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
        ?string $notes = null,
        ?int $unitId = null
    ): TransactionLedger {
        return DB::transaction(function () use (
            $productId, $locationType, $locationId, $transactionType, $quantity,
            $unitPrice, $toLocationType, $toLocationId, $referenceType, $referenceId, $notes, $unitId
        ) {
            $product = Product::findOrFail($productId);
            $baseUnitId = $product->unit_id ? (int) $product->unit_id : null;

            // Stock is kept in the product's own unit. The quantity and price
            // the caller supplied may be in another unit; convert them before
            // anything touches product_stock. A missing conversion is treated
            // as 1:1 by the service, so a document can never fail just because
            // a conversion row was not maintained.
            $enteredQuantity = $quantity;
            $factor = $this->unitConversion->factor($unitId, $baseUnitId);
            $quantity = $enteredQuantity * $factor;
            $unitPrice = $factor != 0.0 ? $unitPrice / $factor : $unitPrice;
            $auditUnitId = $unitId ?: $baseUnitId;

            $stock = ProductStock::firstOrCreate(
                ['product_id' => $productId, 'location_type' => $locationType, 'location_id' => $locationId],
                ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0]
            );

            $quantityBefore = $stock->quantity;
            $quantityAfter = $quantityBefore;
            $absQty = abs($quantity);

            $isInflow = in_array($transactionType, [
                'opening_stock', 'purchase',
                'transfer_in', 'sales_return'
            ]);
            $isOutflow = in_array($transactionType, [
                'issue', 'consumption', 'transfer_out',
                'adjustment', 'damage', 'sales', 'purchase_return'
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
                    'unit_id' => $auditUnitId,
                    'location_type' => $toLocationType,
                    'location_id' => $toLocationId,
                    'to_location_type' => null,
                    'to_location_id' => null,
                    'transaction_type' => 'transfer_in',
                    'quantity' => $absQty,
                    'entered_quantity' => $enteredQuantity,
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
                'unit_id' => $auditUnitId,
                'location_type' => $locationType,
                'location_id' => $locationId,
                'to_location_type' => $isTransfer ? $toLocationType : null,
                'to_location_id' => $isTransfer ? $toLocationId : null,
                'transaction_type' => $transactionType,
                'quantity' => $isOutflow || $isTransfer ? -$absQty : $absQty,
                'entered_quantity' => $enteredQuantity,
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
