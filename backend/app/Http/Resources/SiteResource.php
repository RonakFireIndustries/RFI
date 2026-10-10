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
            'geo_fencing_enabled' => (bool) $this->geo_fencing_enabled,
            'status' => $this->status,
            'site_manager_id' => $this->site_manager_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'site_manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager?->id,
                    'name' => $this->manager?->full_name ?? null,
                ];
            }),

            // Building survey profile - populated when a building is promoted
            // into a site, otherwise null for sites created from scratch.
            'society_name' => $this->society_name,
            'building_type' => $this->building_type,
            'area' => $this->area,
            'google_place_id' => $this->google_place_id,
            'construction_year' => $this->construction_year,
            'occupancy_certificate_year' => $this->occupancy_certificate_year,
            'fire_noc_status' => $this->fire_noc_status,
            'no_of_floors' => $this->no_of_floors,
            'basement_levels' => $this->basement_levels,
            'no_of_wings' => $this->no_of_wings,
            'no_of_flats' => $this->no_of_flats,
            'terrace_available' => $this->terrace_available,
            'refuge_floors' => $this->refuge_floors,
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
            'property_owner' => $this->property_owner,
            'plot_no' => $this->plot_no,
            'contact_name' => $this->contact_name,
            'contact_number' => $this->contact_number,
            'developer_name' => $this->developer_name,
            'developer_contact' => $this->developer_contact,
            'architect_name' => $this->architect_name,
            'architect_contact' => $this->architect_contact,
            'pmc_consultant_name' => $this->pmc_consultant_name,
            'pmc_consultant_contact' => $this->pmc_consultant_contact,
            'provisional_noc' => $this->provisional_noc,
            'provisional_noc_details' => $this->provisional_noc_details,
            'under_construction' => $this->under_construction,

            // Related record counts (only present when explicitly counted).
            'source_buildings_count' => $this->whenCounted('sourceBuildings'),
            'employees_count' => $this->whenCounted('employeeSites'),
            'daily_reports_count' => $this->whenCounted('dailyReports'),
            'stock_count' => $this->whenCounted('stock'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
