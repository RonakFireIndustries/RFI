<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Site;
use App\Services\EmployeeSiteAssignmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeSiteAssignmentController extends Controller
{
    use ApiResponse;

    protected EmployeeSiteAssignmentService $assignmentService;

    public function __construct(EmployeeSiteAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Get active site assignments for an employee.
     */
    public function index(Employee $employee): JsonResponse
    {
        $assignments = $this->assignmentService->getEmployeeSites($employee);

        return $this->success('Employee site assignments retrieved successfully', [
            'assignments' => $assignments
        ]);
    }

    /**
     * Get site assignment history for an employee.
     */
    public function history(Employee $employee): JsonResponse
    {
        $history = $this->assignmentService->getEmployeeSiteHistory($employee);

        return $this->success('Employee site history retrieved successfully', [
            'history' => $history
        ]);
    }

    /**
     * Assign employee to a site.
     */
    public function assign(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'role' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $assignment = $this->assignmentService->assignEmployeeToSite(
            $employee,
            $validated['site_id'],
            $validated['role'] ?? null,
            $validated['remarks'] ?? null
        );

        return $this->success('Employee assigned to site successfully', [
            'assignment' => $assignment->load('site')
        ], [], 201);
    }

    /**
     * Transfer employee from one site to another.
     */
    public function transfer(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'current_site_id' => 'required|exists:sites,id',
            'new_site_id' => 'required|exists:sites,id|different:current_site_id',
            'role' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $assignment = $this->assignmentService->transferEmployee(
            $employee,
            $validated['current_site_id'],
            $validated['new_site_id'],
            $validated['role'] ?? null,
            $validated['remarks'] ?? null
        );

        return $this->success('Employee transferred to new site successfully', [
            'assignment' => $assignment->load('site')
        ]);
    }

    /**
     * Remove employee from a site.
     */
    public function remove(Request $request, Employee $employee, Site $site): JsonResponse
    {
        $remarks = $request->input('remarks');

        $this->assignmentService->removeEmployeeFromSite($employee, $site->id, $remarks);

        return $this->success('Employee removed from site successfully');
    }

    /**
     * Get current site of the employee.
     */
    public function currentSite(Employee $employee): JsonResponse
    {
        $assignment = $employee->employeeSites()->with('site')->first();

        return $this->success('Current site retrieved successfully', [
            'site' => $assignment ? $assignment->site : null,
            'assignment' => $assignment
        ]);
    }

    /**
     * Get employees assigned to a site.
     */
    public function siteEmployees(Site $site): JsonResponse
    {
        $employees = $this->assignmentService->getSiteEmployees($site);

        return $this->success('Site employees retrieved successfully', [
            'employees' => $employees
        ]);
    }
}
