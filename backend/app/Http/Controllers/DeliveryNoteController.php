<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return DeliveryNote::with(['items.product'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'delivery_date' => 'required|date',
            'items' => 'required|array',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.delivered_quantity' => 'required|integer|min:1',
        ]);

        $so = SalesOrder::findOrFail($request->sales_order_id);
        
        if (!in_array($so->status, ['Approved', 'Partially Delivered'])) {
            return response()->json(['message' => 'SO must be Approved or Partially Delivered to process a Delivery Note'], 400);
        }

        DB::beginTransaction();
        try {
            $delivery = DeliveryNote::create([
                'delivery_number' => 'DEL-' . time(),
                'sales_order_id' => $so->id,
                'delivered_by' => Auth::id(),
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes,
            ]);

            $fullyDelivered = true;

            foreach ($request->items as $item) {
                $soItem = SalesOrderItem::findOrFail($item['sales_order_item_id']);
                
                if ($soItem->sales_order_id !== $so->id) {
                    throw new \Exception("Item mismatch.");
                }

                $deliveredQty = $item['delivered_quantity'];
                
                DeliveryNoteItem::create([
                    'delivery_note_id' => $delivery->id,
                    'sales_order_item_id' => $soItem->id,
                    'product_id' => $soItem->product_id,
                    'delivered_quantity' => $deliveredQty,
                ]);

                $soItem->increment('delivered_quantity', $deliveredQty);

                if ($soItem->delivered_quantity < $soItem->quantity) {
                    $fullyDelivered = false;
                }

                // Decrease Inventory
                $this->inventoryService->removeStock(
                    $soItem->product_id,
                    $deliveredQty,
                    'delivery_note',
                    $delivery->id,
                    "Delivery Note #{$delivery->delivery_number} processed"
                );
            }

            $so->status = $fullyDelivered ? 'Fully Delivered' : 'Partially Delivered';
            $so->save();

            DB::commit();
            return response()->json($delivery->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
