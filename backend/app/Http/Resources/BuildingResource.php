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
            'society_name' => $this->society_name,
            'building_type' => $this->building_type,
            'address' => $this->address,
            'area' => $this->area,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'google_place_id' => $this->google_place_id,
            'no_of_floors' => $this->no_of_floors,
            'no_of_wings' => $this->no_of_wings,
            'no_of_flats' => $this->no_of_flats,
            'commercial_shops_available' => $this->commercial_shops_available,
            'no_of_staircase' => $this->no_of_staircase,
            'no_of_lifts' => $this->no_of_lifts,
            'no_of_exits_entry' => $this->no_of_exits_entry,
            'fire_safety_available' => $this->fire_safety_available,
            'fire_safety_type' => $this->fire_safety_type,
            'equipment_condition_notes' => $this->equipment_condition_notes,
            'last_serviced_date' => $this->last_serviced_date?->format('Y-m-d'),
            'last_amc_company' => $this->last_amc_company,
            'overall_equipment_condition' => $this->overall_equipment_condition,
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
            'construction_year' => $this->construction_year,
            'occupancy_certificate_year' => $this->occupancy_certificate_year,
            'fire_noc_status' => $this->fire_noc_status,
            'basement_levels' => $this->basement_levels,
            'terrace_available' => $this->terrace_available,
            'refuge_floors' => $this->refuge_floors,
            'site_id' => $this->site_id,
            'site' => $this->whenLoaded('site'),
            'status' => $this->status,
            'statuses' => $this->whenLoaded('statuses'),
            'wings' => $this->whenLoaded('wings'),
            'fire_systems' => $this->whenLoaded('fireSystems'),
            'contacts' => $this->whenLoaded('contacts'),
            'active_opportunity' => $this->whenLoaded('activeOpportunity'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
