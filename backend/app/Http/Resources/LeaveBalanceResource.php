<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'allocated' => $this->allocated,
            'used' => $this->used,
            'remaining' => $this->remaining,
            'carry_forward' => $this->carry_forward,
            'expired' => $this->expired,
            'year' => $this->year,
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
        ];
    }
}
