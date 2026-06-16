<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name ?? null,
            'email' => $this->user->email ?? null,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'branch_id' => $this->user?->branch_id,
                'branch' => $this->user?->relationLoaded('branch') && $this->user?->branch ? [
                    'id' => $this->user->branch->id,
                    'name' => $this->user->branch->name,
                ] : null,
            ]),
            'branch' => $this->whenLoaded('branch', fn () => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ]),
            'department_id' => $this->department_id,
            'designation_id' => $this->designation_id,
            'department_name' => $this->relationLoaded('department')
                ? $this->getRelation('department')?->name
                : $this->getAttribute('department'),
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->getRelation('department')?->id,
                'name' => $this->getRelation('department')?->name,
            ]),
            'designation' => $this->whenLoaded('designation', fn () => [
                'id' => $this->getRelation('designation')?->id,
                'name' => $this->getRelation('designation')?->name,
            ]),
            'role' => $this->user?->getRoleNames()->first(),
            'permissions' => $this->user?->getAllPermissions()->pluck('name') ?? [],
            'salary' => $this->salary,
            'shift' => $this->shift,
            'joining_date' => $this->joining_date,
            'status' => $this->status,
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
            'leaves' => LeaveResource::collection($this->whenLoaded('leaves')),
            'payroll' => PayrollResource::collection($this->whenLoaded('payroll')),
            'sites' => SiteResource::collection($this->whenLoaded('sites')),
            'daily_reports' => DailyReportResource::collection($this->whenLoaded('dailyReports')),
            'created_at' => $this->created_at,
        ];
    }
}
