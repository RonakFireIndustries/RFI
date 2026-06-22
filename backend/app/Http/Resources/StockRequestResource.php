<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product'),
            'from_location_type' => $this->from_location_type,
            'from_location_id' => $this->from_location_id,
            'from_locationable' => $this->whenLoaded('from_locationable'),
            'to_location_type' => $this->to_location_type,
            'to_location_id' => $this->to_location_id,
            'to_locationable' => $this->whenLoaded('to_locationable'),
            'quantity' => (float) $this->quantity,
            'approved_quantity' => (float) $this->approved_quantity,
            'issued_quantity' => (float) $this->issued_quantity,
            'received_quantity' => (float) $this->received_quantity,
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'requester' => $this->whenLoaded('requester', fn() => $this->requester?->name),
            'approved_by' => $this->approved_by,
            'approver' => $this->whenLoaded('approver', fn() => $this->approver?->name),
            'issued_by' => $this->issued_by,
            'issuer' => $this->whenLoaded('issuer', fn() => $this->issuer?->name),
            'received_by' => $this->received_by,
            'receiver' => $this->whenLoaded('receiver', fn() => $this->receiver?->name),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
