<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportGenerationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_id' => $this->report_id,
            'report' => new ReportResource($this->whenLoaded('report')),
            'schedule_id' => $this->schedule_id,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'file_size_for_humans' => $this->file_size_for_humans,
            'generated_by' => $this->generated_by,
            'generator' => $this->whenLoaded('generator', fn() => $this->generator?->name),
            'generated_at' => $this->generated_at,
            'parameters' => $this->parameters,
            'status' => $this->status,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
