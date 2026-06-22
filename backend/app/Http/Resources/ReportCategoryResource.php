<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'reports_count' => $this->whenCounted('activeReports', fn() => $this->active_reports_count ?? $this->activeReports()->count()),
            'reports' => ReportResource::collection($this->whenLoaded('activeReports')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
