<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\EmployeeShiftAssignmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeShiftAssignmentController extends Controller
{
    use ApiResponse;

    protected EmployeeShiftAssignmentService $assignmentService;

    public function __construct(EmployeeShiftAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Get the employee's current shift assignment.
     */
    public function index(Employee $employee): JsonResponse
    {
        $assignment = $this->assignmentService->getEmployeeShift($employee);

        return $this->success('Employee shift assignment retrieved successfully', [
            'assignment' => $assignment,
            'shift' => $assignment?->shift,
        ]);
    }

    /**
     * Get shift assignment history for an employee.
     */
    public function history(Employee $employee): JsonResponse
    {
        $history = $this->assignmentService->getEmployeeShiftHistory($employee);

        return $this->success('Employee shift history retrieved successfully', [
            'history' => $history
        ]);
    }

    /**
     * Assign (or change) the employee's shift.
     */
    public function assign(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'remarks' => 'nullable|string',
        ]);

        $assignment = $this->assignmentService->assignEmployeeShift(
            $employee,
            $validated['shift_id'],
            $validated['remarks'] ?? null
        );

        return $this->success('Employee shift assigned successfully', [
            'assignment' => $assignment->load('shift')
        ], [], 201);
    }

    /**
     * Change the employee's shift (alias of assign with previous-shift logging).
     */
    public function transfer(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'new_shift_id' => 'required|exists:shifts,id',
            'remarks' => 'nullable|string',
        ]);

        $assignment = $this->assignmentService->assignEmployeeShift(
            $employee,
            $validated['new_shift_id'],
            $validated['remarks'] ?? null
        );

        return $this->success('Employee shift changed successfully', [
            'assignment' => $assignment->load('shift')
        ]);
    }

    /**
     * Remove the employee's current shift assignment.
     */
    public function remove(Request $request, Employee $employee, ?Shift $shift = null): JsonResponse
    {
        $remarks = $request->input('remarks');

        $this->assignmentService->removeEmployeeShift($employee, $remarks);

        return $this->success('Employee removed from shift successfully');
    }

    /**
     * Get the current shift of the employee.
     */
    public function currentShift(Employee $employee): JsonResponse
    {
        $assignment = $this->assignmentService->getEmployeeShift($employee);

        return $this->success('Current shift retrieved successfully', [
            'shift' => $assignment?->shift,
            'assignment' => $assignment,
        ]);
    }

    /**
     * Get employees assigned to a shift.
     */
    public function shiftEmployees(Shift $shift): JsonResponse
    {
        $employees = $this->assignmentService->getShiftEmployees($shift);

        return $this->success('Shift employees retrieved successfully', [
            'employees' => $employees
        ]);
    }
}
