<?php

namespace App\Services;

use App\Models\LeaveType;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveTypeService
{
    public function getLeaveTypes(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LeaveType::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function createLeaveType(array $data): LeaveType
    {
        return LeaveType::create($data);
    }

    public function updateLeaveType(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($data);
        return $leaveType;
    }

    public function deleteLeaveType(LeaveType $leaveType): void
    {
        $leaveType->delete();
    }
}
