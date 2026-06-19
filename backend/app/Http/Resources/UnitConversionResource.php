<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitConversionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_unit_id' => $this->from_unit_id,
            'from_unit' => $this->whenLoaded('fromUnit', fn() => $this->fromUnit),
            'to_unit_id' => $this->to_unit_id,
            'to_unit' => $this->whenLoaded('toUnit', fn() => $this->toUnit),
            'conversion_factor' => (float) $this->conversion_factor,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
