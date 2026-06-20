<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransfer;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = InventoryTransfer::with(['product', 'requester', 'approver']);

        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $transfer = InventoryTransfer::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'status' => 'pending',
            'requested_by' => $request->user()->id,
            'notes' => $request->notes,
        ]);

        return response()->json($transfer, 201);
    }

    public function updateStatus(Request $request, InventoryTransfer $transfer)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,completed'
        ]);

        if ($transfer->status !== 'pending' && $request->status === 'approved') {
            return response()->json(['message' => 'Transfer is already processed'], 400);
        }

        DB::transaction(function () use ($transfer, $request) {
            $transfer->status = $request->status;
            
            if ($request->status === 'approved') {
                $transfer->approved_by = $request->user()->id;
            }
            
            $transfer->save();
        });

        return response()->json($transfer);
    }
}
