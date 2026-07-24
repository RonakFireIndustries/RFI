<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'building_type' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'area' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'no_of_floors' => ['nullable', 'integer', 'min:0'],
            'no_of_wings' => ['nullable', 'string', 'max:50'],
            'no_of_flats' => ['nullable', 'integer', 'min:0'],
            'commercial_shops_available' => ['boolean'],
            'no_of_staircase' => ['nullable', 'integer', 'min:0'],
            'no_of_lifts' => ['nullable', 'integer', 'min:0'],
            'no_of_exits_entry' => ['nullable', 'integer', 'min:0'],
            'fire_safety_available' => ['boolean'],
            'fire_safety_type' => ['nullable', 'string', 'max:255'],
            'equipment_condition_notes' => ['nullable', 'string'],
            'last_serviced_date' => ['nullable', 'date'],
            'last_amc_company' => ['nullable', 'string', 'max:255'],
            'overall_equipment_condition' => ['nullable', 'string', 'max:255'],
            'under_construction' => ['boolean'],
            'property_owner' => ['nullable', 'string', 'max:255'],
            'plot_no' => ['nullable', 'string', 'max:100'],
            'developer_name' => ['nullable', 'string', 'max:255'],
            'developer_contact' => ['nullable', 'string', 'max:255'],
            'architect_name' => ['nullable', 'string', 'max:255'],
            'architect_contact' => ['nullable', 'string', 'max:255'],
            'pmc_consultant_name' => ['nullable', 'string', 'max:255'],
            'pmc_consultant_contact' => ['nullable', 'string', 'max:255'],
            'provisional_noc' => ['boolean'],
            'provisional_noc_details' => ['nullable', 'string'],
            'construction_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'occupancy_certificate_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'fire_noc_status' => ['nullable', 'string', 'in:Unknown,Applied,Approved,Expired,Rejected'],
            'basement_levels' => ['nullable', 'integer', 'min:0'],
            'terrace_available' => ['boolean'],
            'refuge_floors' => ['nullable', 'integer', 'min:0'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ];
    }
}
