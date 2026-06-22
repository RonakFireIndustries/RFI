<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new ReportCategoryResource($this->whenLoaded('category')),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'route' => $this->route,
            'api_endpoint' => $this->api_endpoint,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn() => $this->creator?->name),
            'last_generated_at' => $this->last_generated_at,
            'parameters' => $this->parameters,
            'sort_order' => $this->sort_order,
            'schedules_count' => $this->whenCounted('schedules'),
            'active_schedules_count' => $this->whenCounted('activeSchedules'),
            'generations_count' => $this->whenCounted('generations'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
