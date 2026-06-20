<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'site_id' => $this->site_id,
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'work_description' => $this->work_description,
            'tasks_completed' => $this->tasks_completed,
            'hours_worked' => $this->hours_worked,
            'issues_faced' => $this->issues_faced,
            'materials_used' => $this->materials_used,
            'equipment_used' => $this->equipment_used,
            'supervisor_remarks' => $this->supervisor_remarks,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'approved_at' => $this->approved_at,
            'approved_by' => $this->approved_by,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'site' => new SiteResource($this->whenLoaded('site')),
            'approver' => new UserResource($this->whenLoaded('approver')),
            'histories' => $this->whenLoaded('histories'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
