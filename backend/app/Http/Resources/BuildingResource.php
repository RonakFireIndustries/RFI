<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'no_of_floors' => $this->no_of_floors,
            'no_of_wings' => $this->no_of_wings,
            'no_of_flats' => $this->no_of_flats,
            'commercial_shops_available' => $this->commercial_shops_available,
            'no_of_staircase' => $this->no_of_staircase,
            'no_of_lifts' => $this->no_of_lifts,
            'no_of_exits_entry' => $this->no_of_exits_entry,
            'fire_safety_available' => $this->fire_safety_available,
            'fire_safety_type' => $this->fire_safety_type,
            'under_construction' => $this->under_construction,
            'property_owner' => $this->property_owner,
            'plot_no' => $this->plot_no,
            'developer_name' => $this->developer_name,
            'developer_contact' => $this->developer_contact,
            'architect_name' => $this->architect_name,
            'architect_contact' => $this->architect_contact,
            'pmc_consultant_name' => $this->pmc_consultant_name,
            'pmc_consultant_contact' => $this->pmc_consultant_contact,
            'provisional_noc' => $this->provisional_noc,
            'provisional_noc_details' => $this->provisional_noc_details,
            'site_id' => $this->site_id,
            'site' => $this->whenLoaded('site'),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
