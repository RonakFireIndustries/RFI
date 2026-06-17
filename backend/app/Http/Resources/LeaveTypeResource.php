<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'annual_allocation' => $this->annual_allocation,
            'carry_forward' => $this->carry_forward,
            'max_consecutive_days' => $this->max_consecutive_days,
            'requires_approval' => $this->requires_approval,
            'status' => $this->status,
        ];
    }
}
