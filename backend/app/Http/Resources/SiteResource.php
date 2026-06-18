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
            'code' => $this->code,
            'client_details' => $this->client_details,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'pincode' => $this->pincode,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'email' => $this->email,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'allowed_radius' => $this->allowed_radius ?? 100,
            'status' => $this->status,
            'site_manager_id' => $this->site_manager_id,
            'site_manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager?->id,
                    'name' => $this->manager?->full_name ?? null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
