<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_id' => $this->report_id,
            'report' => new ReportResource($this->whenLoaded('report')),
            'name' => $this->name,
            'frequency' => $this->frequency,
            'config' => $this->config,
            'next_run_at' => $this->next_run_at,
            'last_run_at' => $this->last_run_at,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn() => $this->creator?->name),
            'recipients' => $this->recipients,
            'format' => $this->format,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
