<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceLocationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'name' => $this->employee->full_name ?? $this->employee->user->name ?? null,
                'emp_id' => $this->employee->emp_id,
            ]),
            'site' => $this->whenLoaded('site', fn () => [
                'id' => $this->site->id,
                'name' => $this->site->name,
                'latitude' => $this->site->latitude,
                'longitude' => $this->site->longitude,
                'allowed_radius' => $this->site->allowed_radius,
            ]),
            'date' => $this->date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'checkin_latitude' => $this->checkin_latitude,
            'checkin_longitude' => $this->checkin_longitude,
            'checkout_latitude' => $this->checkout_latitude,
            'checkout_longitude' => $this->checkout_longitude,
            'checkin_distance' => $this->checkin_distance,
            'checkout_distance' => $this->checkout_distance,
            'accuracy' => $this->accuracy,
            'location_verified' => $this->location_verified,
            'device_info' => $this->device_info,
            'browser_info' => $this->browser_info,
            'ip_address' => $this->ip_address,
            'working_hours' => $this->working_hours,
            'status' => $this->status,
        ];
    }
}
