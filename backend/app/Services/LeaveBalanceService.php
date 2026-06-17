<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveBalanceService
{
    public function getBalances(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LeaveBalance::with(['employee', 'leaveType']);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        return $query->paginate($perPage);
    }

    public function initializeBalances(Employee $employee, int $year): void
    {
        $leaveTypes = LeaveType::where('status', 'Active')->get();

        foreach ($leaveTypes as $type) {
            LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'year' => $year,
                ],
                [
                    'allocated' => $type->annual_allocation,
                    'used' => 0,
                    'remaining' => $type->annual_allocation,
                    'carry_forward' => 0,
                    'expired' => 0,
                ]
            );
        }
    }

    public function deductBalance(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $balance = LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if ($balance && $balance->remaining >= $days) {
            $balance->used += $days;
            $balance->remaining -= $days;
            $balance->save();
        } else {
            throw new \Exception("Insufficient leave balance.");
        }
    }

    public function refundBalance(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $balance = LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if ($balance) {
            $balance->used -= $days;
            $balance->remaining += $days;
            $balance->save();
        }
    }
}
