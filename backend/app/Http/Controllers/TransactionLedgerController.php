<?php

namespace App\Http\Controllers;

use App\Models\TransactionLedger;
use App\Http\Resources\TransactionLedgerResource;
use App\Http\Requests\StoreTransactionRequest;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class TransactionLedgerController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $query = TransactionLedger::with(['product', 'locationable', 'toLocationable', 'creator']);

        if ($request->has('location_type') && $request->has('location_id')) {
            $query->where('location_type', $request->location_type)->where('location_id', $request->location_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return TransactionLedgerResource::collection(
            $query->latest()->paginate($request->per_page ?? 50)
        );
    }

    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();

        try {
            $transaction = $this->inventoryService->recordTransaction(
                productId: $data['product_id'],
                locationType: $data['location_type'],
                locationId: $data['location_id'],
                transactionType: $data['transaction_type'],
                quantity: $data['quantity'],
                unitPrice: $data['unit_price'] ?? 0,
                toLocationType: $data['to_location_type'] ?? null,
                toLocationId: $data['to_location_id'] ?? null,
                referenceType: $data['reference_type'] ?? null,
                referenceId: $data['reference_id'] ?? null,
                notes: $data['notes'] ?? null
            );

            return new TransactionLedgerResource(
                $transaction->load(['product', 'locationable', 'toLocationable', 'creator'])
            );
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(TransactionLedger $transactionLedger)
    {
        return new TransactionLedgerResource(
            $transactionLedger->load(['product', 'locationable', 'toLocationable', 'creator'])
        );
    }

    public function summary(Request $request)
    {
        $query = TransactionLedger::query();

        if ($request->has('location_type') && $request->has('location_id')) {
            $query->where('location_type', $request->location_type)->where('location_id', $request->location_id);
        }

        $inflow = (clone $query)->whereIn('transaction_type', [
            'opening_stock', 'purchase', 'purchase_return', 'transfer_in', 'sales_return'
        ])->sum('quantity');

        $outflow = (clone $query)->whereIn('transaction_type', [
            'issue', 'consumption', 'transfer_out', 'adjustment', 'damage', 'sales'
        ])->sum(\DB::raw('ABS(quantity)'));

        return response()->json([
            'total_inflow' => $inflow,
            'total_outflow' => $outflow,
            'net_movement' => $inflow - $outflow,
        ]);
    }
}
