<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gst_number' => $this->gst_number,
            'address' => $this->address,
            // Not `whenLoaded` alone: with the relation eager-loaded but empty
            // it returns plain null, which would serialise as `"ledger": null`
            // and make the profile render "null · null". MissingValue keeps the
            // key out of the payload entirely, and skips the lazy load too.
            'ledger' => $this->relationLoaded('ledger') && $this->ledger
                ? [
                    'id' => $this->ledger->id,
                    'name' => $this->ledger->name,
                    'group' => $this->ledger->group?->name,
                    'status' => $this->ledger->status,
                ]
                : new MissingValue(),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'purchase_orders' => $this->whenLoaded('purchaseOrders', fn () => $this->purchaseOrders->map(fn ($order) => [
                'id' => $order->id,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at,
            ])),
            'products_count' => $this->products_count ?? $this->whenCounted('products'),
            'purchase_orders_count' => $this->purchase_orders_count ?? $this->whenCounted('purchaseOrders'),
            'balance' => $this->balance ?? null,
            'category' => $this->category ?? 'General',
            'status' => $this->status ?? 'Active',
            'created_at' => $this->created_at,
        ];
    }
}
