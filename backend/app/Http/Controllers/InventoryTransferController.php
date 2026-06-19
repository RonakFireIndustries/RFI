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
        $query = InventoryTransfer::with(['sourceBranch', 'destinationBranch', 'product', 'requester', 'approver']);

        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'destination_branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $sourceBranchId = $request->header('X-Branch-Id') ?? $request->source_branch_id ?? $request->user()->branch_id;

        if (!$sourceBranchId) {
            return response()->json(['message' => 'Source branch is required'], 400);
        }

        $sourceInventory = Inventory::withoutGlobalScopes()
            ->where('branch_id', $sourceBranchId)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$sourceInventory || $sourceInventory->quantity < $request->quantity) {
            return response()->json(['message' => 'Insufficient stock in source branch'], 400);
        }

        $transfer = InventoryTransfer::create([
            'source_branch_id' => $sourceBranchId,
            'destination_branch_id' => $request->destination_branch_id,
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

        $user = $request->user();
            if ($user->branch_id !== $transfer->destination_branch_id && $user->branch_id !== $transfer->source_branch_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

        if ($transfer->status !== 'pending' && $request->status === 'approved') {
            return response()->json(['message' => 'Transfer is already processed'], 400);
        }

        DB::transaction(function () use ($transfer, $request, $user) {
            $transfer->status = $request->status;
            
            if ($request->status === 'approved') {
                $transfer->approved_by = $user->id;
                
                $sourceInv = Inventory::withoutGlobalScopes()
                    ->firstOrCreate(
                        ['branch_id' => $transfer->source_branch_id, 'product_id' => $transfer->product_id],
                        ['quantity' => 0]
                    );
                $sourceInv->quantity -= $transfer->quantity;
                $sourceInv->save();

                $destInv = Inventory::withoutGlobalScopes()
                    ->firstOrCreate(
                        ['branch_id' => $transfer->destination_branch_id, 'product_id' => $transfer->product_id],
                        ['quantity' => 0]
                    );
                $destInv->quantity += $transfer->quantity;
                $destInv->save();
            }
            
            $transfer->save();
        });

        return response()->json($transfer);
    }
}
