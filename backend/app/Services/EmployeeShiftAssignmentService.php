<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftHistory;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeShiftAssignmentService
{
    /**
     * Get the employee's current shift assignment.
     */
    public function getEmployeeShift(Employee $employee): ?EmployeeShift
    {
        return $employee->employeeShifts()->with('shift')->first();
    }

    /**
     * Get shift assignment history for an employee.
     */
    public function getEmployeeShiftHistory(Employee $employee)
    {
        return $employee->shiftHistories()
            ->with(['previousShift', 'newShift', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Assign (or change) the employee's shift.
     *
     * An employee holds a single active shift, so an existing assignment is
     * replaced and the change is recorded in the history log (previous shift ->
     * new shift). A brand new assignment logs previous_shift_id = null.
     */
    public function assignEmployeeShift(Employee $employee, int $shiftId, ?string $remarks = null): EmployeeShift
    {
        return DB::transaction(function () use ($employee, $shiftId, $remarks) {
            $existing = EmployeeShift::where('employee_id', $employee->id)->first();
            $previousShiftId = $existing?->shift_id;

            if ($existing) {
                $existing->delete();
            }

            $assignment = EmployeeShift::create([
                'employee_id' => $employee->id,
                'shift_id' => $shiftId,
                'assigned_at' => now(),
                'remarks' => $remarks,
            ]);

            EmployeeShiftHistory::create([
                'employee_id' => $employee->id,
                'assigned_by_id' => Auth::id(),
                'previous_shift_id' => $previousShiftId,
                'new_shift_id' => $shiftId,
                'assigned_at' => now(),
                'effective_date' => now(),
                'remarks' => $remarks ?? ($previousShiftId ? 'Shift changed' : 'Assigned to shift'),
            ]);

            return $assignment;
        });
    }

    /**
     * Remove the employee's current shift assignment.
     */
    public function removeEmployeeShift(Employee $employee, ?string $remarks = null): void
    {
        DB::transaction(function () use ($employee, $remarks) {
            $existing = EmployeeShift::where('employee_id', $employee->id)->first();

            if (!$existing) {
                return;
            }

            $previousShiftId = $existing->shift_id;
            $existing->delete();

            EmployeeShiftHistory::create([
                'employee_id' => $employee->id,
                'assigned_by_id' => Auth::id(),
                'previous_shift_id' => $previousShiftId,
                'new_shift_id' => null,
                'assigned_at' => now(),
                'remarks' => $remarks ?? 'Removed from shift',
            ]);
        });
    }

    /**
     * Get employees currently assigned to a shift.
     */
    public function getShiftEmployees(Shift $shift)
    {
        return Employee::with(['designation', 'department'])
            ->whereHas('employeeShifts', function ($query) use ($shift) {
                $query->where('shift_id', $shift->id);
            })->get();
    }
}
