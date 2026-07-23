<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static function canManageSalesPrice(Request $request): bool
    {
        $financeRoles = ['Admin', 'Accountant'];
        return $request->user()?->roles->pluck('name')->intersect($financeRoles)->isNotEmpty();
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'product_code' => $this->product_code,
            'name' => $this->name,
            'hsn_code' => $this->hsn_code,
            'dimension' => $this->dimension,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category'),
            'unit_id' => $this->unit_id,
            'unit' => $this->whenLoaded('unit'),
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier'),
            $this->mergeWhen(static::canManageSalesPrice($request), [
                'purchase_price' => (float) $this->purchase_price,
                'selling_price' => (float) $this->selling_price,
            ]),
            'cost_price' => (float) $this->cost_price,
            'opening_stock' => (float) $this->opening_stock,
            'reorder_level' => (float) $this->reorder_level,
            'min_stock' => (float) $this->min_stock,
            'max_stock' => (float) $this->max_stock,
            'description' => $this->description,
            'status' => $this->status,
            'stock' => ProductStockResource::collection($this->whenLoaded('stock')),
            'total_stock' => $this->stock->sum('quantity'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
