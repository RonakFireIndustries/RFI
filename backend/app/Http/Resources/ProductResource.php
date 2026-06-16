<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'supplier' => $this->whenLoaded('supplier', fn () => [
                'id' => $this->supplier?->id,
                'name' => $this->supplier?->name,
            ]),
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'gst_percentage' => $this->gst_percentage,
            'inventory_quantity' => $this->inventories_sum_quantity,
            'inventories' => InventoryResource::collection($this->whenLoaded('inventories')),
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
