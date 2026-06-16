<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'client_details' => $this->client_details,
            'status' => $this->status,
            'site_manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager?->id,
                    'name' => $this->manager?->user?->name ?? null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
