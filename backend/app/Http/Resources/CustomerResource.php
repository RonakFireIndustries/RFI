<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class CustomerResource extends JsonResource
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
            'sales_orders' => $this->whenLoaded('salesOrders', fn () => $this->salesOrders->map(fn ($order) => [
                'id' => $order->id,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'products_count' => $order->relationLoaded('products') ? $order->products->count() : null,
                'created_at' => $order->created_at,
            ])),
            'orders_count' => $this->orders_count ?? $this->whenCounted('salesOrders'),
            'balance' => $this->balance ?? null,
            'total_revenue' => $this->total_revenue ?? null,
            'outstanding_balance' => $this->outstanding_balance ?? null,
            'status' => $this->status ?? 'Active',
            'created_at' => $this->created_at,
        ];
    }
}
