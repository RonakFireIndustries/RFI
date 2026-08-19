<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'no_of_floors' => ['nullable', 'integer', 'min:0'],
            'no_of_wings' => ['nullable', 'integer', 'min:0'],
            'wings' => ['nullable', 'array'],
            'wings.*.name' => ['required_with:wings', 'string', 'max:255'],
            'wings.*.floors' => ['nullable', 'integer', 'min:0'],
            'wings.*.flats_per_floor' => ['nullable', 'integer', 'min:0'],
            'wings.*.flat_configuration' => ['nullable', 'string', 'max:255'],
            'wings.*.total_flats' => ['nullable', 'integer', 'min:0'],
            'wings.*.floors_data' => ['nullable', 'array'],
            'wings.*.floors_data.*.name' => ['required_with:wings.*.floors_data', 'string', 'max:255'],
            'wings.*.floors_data.*.floor_number' => ['nullable', 'integer'],
            'wings.*.floors_data.*.type' => ['nullable', 'string', 'max:255'],
            'wings.*.floors_data.*.flats_data' => ['nullable', 'array'],
            'wings.*.floors_data.*.flats_data.*.name' => ['required_with:wings.*.floors_data.*.flats_data', 'string', 'max:255'],
            'wings.*.floors_data.*.flats_data.*.flat_number' => ['nullable', 'string', 'max:255'],
            'wings.*.floors_data.*.flats_data.*.bhk_type' => ['nullable', 'string', 'max:50'],
            'wings.*.floors_data.*.flats_data.*.area' => ['nullable', 'numeric', 'min:0'],
            'no_of_flats' => ['nullable', 'integer', 'min:0'],
            'commercial_shops_available' => ['boolean'],
            'no_of_staircase' => ['nullable', 'integer', 'min:0'],
            'no_of_lifts' => ['nullable', 'integer', 'min:0'],
            'no_of_exits_entry' => ['nullable', 'integer', 'min:0'],
            'fire_safety_available' => ['boolean'],
            'fire_safety_type' => ['nullable', 'string', 'max:255'],
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
            'site_id' => ['nullable', 'exists:sites,id'],
            'status' => ['sometimes', 'required', 'string', 'in:Active,Inactive'],
        ];
    }
}
