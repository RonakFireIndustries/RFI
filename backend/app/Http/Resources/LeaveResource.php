<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'total_days' => $this->total_days,
            'is_half_day' => $this->is_half_day,
            'status' => $this->status,
            'reason' => $this->reason,
            'attachment_path' => $this->attachment_path ? asset('storage/' . $this->attachment_path) : null,
            'comments' => $this->comments,
            'applied_at' => $this->applied_at,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee?->id,
                'name' => $this->employee?->user?->name,
                'full_name' => $this->employee?->user?->name,
                'first_name' => $this->employee?->user?->name,
                'last_name' => '',
                'email' => $this->employee?->user?->email,
                'department' => $this->employee?->relationLoaded('department') && $this->employee?->getRelation('department') ? [
                    'id' => $this->employee->getRelation('department')->id,
                    'name' => $this->employee->getRelation('department')->name,
                ] : null,
            ]),
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'approver' => $this->whenLoaded('approver', fn () => [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email,
            ]),
            'histories' => $this->whenLoaded('histories'),
        ];
    }
}
