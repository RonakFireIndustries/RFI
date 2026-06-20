<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->whenLoaded('product')),
            'transactions' => $this->whenLoaded('transactions', fn () => $this->transactions->map(fn ($transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'quantity' => $transaction->quantity,
                'notes' => $transaction->notes,
                'created_at' => $transaction->created_at,
            ])),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
