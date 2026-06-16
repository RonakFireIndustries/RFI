<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'items' => $this->whenLoaded('items', fn () => InvoiceItemResource::collection($this->items)),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount' => $this->discount,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
