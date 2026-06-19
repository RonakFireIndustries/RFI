<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_number' => $this->transaction_number,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product'),
            'location_type' => $this->location_type,
            'location_id' => $this->location_id,
            'locationable' => $this->whenLoaded('locationable'),
            'to_location_type' => $this->to_location_type,
            'to_location_id' => $this->to_location_id,
            'to_locationable' => $this->whenLoaded('toLocationable'),
            'transaction_type' => $this->transaction_type,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,
            'quantity_before' => (float) $this->quantity_before,
            'quantity_after' => (float) $this->quantity_after,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn() => $this->creator?->name),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
