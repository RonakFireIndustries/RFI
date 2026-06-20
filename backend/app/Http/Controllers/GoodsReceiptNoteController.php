<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GoodsReceiptNote;
use App\Models\GrnItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class GoodsReceiptNoteController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return GoodsReceiptNote::with(['items.product'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'items' => 'required|array',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|integer|min:1',
        ]);

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);
        
        if (!in_array($po->status, ['Approved', 'Partially Received'])) {
            return response()->json(['message' => 'PO must be Approved or Partially Received to process a GRN'], 400);
        }

        DB::beginTransaction();
        try {
            $grn = GoodsReceiptNote::create([
                'grn_number' => 'GRN-' . time(),
                'purchase_order_id' => $po->id,
                'received_by' => Auth::id(),
                'receipt_date' => $request->receipt_date,
                'notes' => $request->notes,
            ]);

            $fullyReceived = true;

            foreach ($request->items as $item) {
                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);
                
                if ($poItem->purchase_order_id !== $po->id) {
                    throw new \Exception("Item mismatch.");
                }

                $receivedQty = $item['received_quantity'];
                
                GrnItem::create([
                    'goods_receipt_note_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'received_quantity' => $receivedQty,
                ]);

                $poItem->increment('received_quantity', $receivedQty);

                if ($poItem->received_quantity < $poItem->quantity) {
                    $fullyReceived = false;
                }

                // Increase Inventory
                $this->inventoryService->addStock(
                    $poItem->product_id,
                    $receivedQty,
                    'goods_receipt_note',
                    $grn->id,
                    "GRN #{$grn->grn_number} processed"
                );
            }

            $po->status = $fullyReceived ? 'Fully Received' : 'Partially Received';
            $po->save();

            DB::commit();
            return response()->json($grn->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
