<?php

namespace App\Services;

use App\Models\StockRequest;
use App\Models\ProductStock;
use App\Models\TransactionLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockRequestService
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function approve(StockRequest $request): StockRequest
    {
        return DB::transaction(function () use ($request) {
            $sourceStock = ProductStock::where('product_id', $request->product_id)
                ->where('location_type', $request->from_location_type)
                ->where('location_id', $request->from_location_id)
                ->first();

            if (!$sourceStock || $sourceStock->quantity < $request->quantity) {
                throw new \Exception('Insufficient stock at source location');
            }

            $request->update([
                'status' => 'approved',
                'approved_quantity' => $request->quantity,
                'approved_by' => Auth::id(),
            ]);

            return $request;
        });
    }

    public function issue(StockRequest $request): StockRequest
    {
        return DB::transaction(function () use ($request) {
            if ($request->status !== 'approved') {
                throw new \Exception('Request must be approved before issuing');
            }

            $this->inventoryService->recordTransaction(
                productId: $request->product_id,
                locationType: $request->from_location_type,
                locationId: $request->from_location_id,
                transactionType: 'transfer_out',
                quantity: $request->approved_quantity,
                unitPrice: 0,
                toLocationType: $request->to_location_type,
                toLocationId: $request->to_location_id,
                referenceType: 'stock_request',
                referenceId: $request->id,
                notes: "Stock request #{$request->request_number}"
            );

            $request->update([
                'status' => 'issued',
                'issued_quantity' => $request->approved_quantity,
                'issued_by' => Auth::id(),
            ]);

            return $request;
        });
    }

    public function receive(StockRequest $request): StockRequest
    {
        return DB::transaction(function () use ($request) {
            if ($request->status !== 'issued') {
                throw new \Exception('Request must be issued before receiving');
            }

            $request->update([
                'status' => 'received',
                'received_quantity' => $request->issued_quantity,
                'received_by' => Auth::id(),
            ]);

            return $request;
        });
    }
}
