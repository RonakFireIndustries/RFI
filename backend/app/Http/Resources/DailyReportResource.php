<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee?->id,
                    'name' => $this->employee?->user?->name ?? null,
                ];
            }),
            'site' => $this->whenLoaded('site', function () {
                return [
                    'id' => $this->site?->id,
                    'name' => $this->site?->name,
                ];
            }),
            'report_date' => $this->report_date,
            'content' => $this->content,
            'created_at' => $this->created_at,
        ];
    }
}
