<?php

namespace App\Http\Controllers;

use App\Models\StockRequest;
use App\Http\Resources\StockRequestResource;
use App\Http\Requests\StoreStockRequestRequest;
use App\Services\StockRequestService;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockRequestController extends Controller
{
    public function __construct(
        private StockRequestService $stockRequestService,
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('inventory.requests.view');
        $query = StockRequest::with([
            'product', 'fromLocationable', 'toLocationable',
            'requester', 'approver', 'issuer', 'receiver'
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('location_type') && $request->has('location_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_location_type', $request->location_type)
                  ->where('from_location_id', $request->location_id)
                  ->orWhere('to_location_type', $request->location_type)
                  ->where('to_location_id', $request->location_id);
            });
        }

        return StockRequestResource::collection($query->latest()->get());
    }

    public function store(StoreStockRequestRequest $request)
    {
        $this->authorize('inventory.requests.create');
        $data = $request->validated();

        $stockRequest = StockRequest::create([
            'request_number' => $this->inventoryService->generateRequestNumber(),
            'product_id' => $data['product_id'],
            'from_location_type' => $data['from_location_type'],
            'from_location_id' => $data['from_location_id'],
            'to_location_type' => $data['to_location_type'],
            'to_location_id' => $data['to_location_id'],
            'quantity' => $data['quantity'],
            'status' => 'requested',
            'requested_by' => $request->user()->id,
            'approved_by' => $data['approved_by'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return new StockRequestResource(
            $stockRequest->load(['product', 'fromLocationable', 'toLocationable', 'requester', 'approver'])
        );
    }

    public function show(StockRequest $stockRequest)
    {
        return new StockRequestResource(
            $stockRequest->load([
                'product', 'fromLocationable', 'toLocationable',
                'requester', 'approver', 'issuer', 'receiver'
            ])
        );
    }

    public function approve(StockRequest $stockRequest)
    {
        $this->authorize('inventory.requests.approve');
        try {
            $result = $this->stockRequestService->approve($stockRequest);
            return new StockRequestResource($result->load(['product', 'fromLocationable', 'toLocationable', 'approver']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function issue(StockRequest $stockRequest)
    {
        try {
            $result = $this->stockRequestService->issue($stockRequest);
            return new StockRequestResource($result->load(['product', 'fromLocationable', 'toLocationable', 'issuer']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function receive(StockRequest $stockRequest)
    {
        try {
            $result = $this->stockRequestService->receive($stockRequest);
            return new StockRequestResource($result->load(['product', 'fromLocationable', 'toLocationable', 'receiver']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
