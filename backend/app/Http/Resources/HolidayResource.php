<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // The date is sent as a plain Y-m-d string: a full timestamp would
            // shift the calendar day for anyone west of Greenwich.
            'date' => $this->date?->format('Y-m-d'),
            'day_of_week' => $this->date?->format('l'),
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
