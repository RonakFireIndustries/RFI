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
     * Assign an employee to several sites at once.
     */
    public function bulkAssign(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'site_ids' => 'required|array|min:1',
            'site_ids.*' => 'required|integer|exists:sites,id',
            'role' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $assignments = $this->assignmentService->assignEmployeeToSites(
            $employee,
            $validated['site_ids'],
            $validated['role'] ?? null,
            $validated['remarks'] ?? null
        );

        $assignments->each->load('site');

        return $this->success('Employee assigned to selected sites successfully', [
            'assignments' => $assignments,
            'assigned_count' => $assignments->count(),
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
     * Get the employee's usable assigned sites and the site relevant to the
     * supplied coordinates.
     *
     * Clients without a GPS fix still receive a deterministic default site;
     * clients that send latitude/longitude get the nearest assigned site,
     * using the same rule as attendance check-in.
     */
    public function currentSite(Request $request, Employee $employee): JsonResponse
    {
        $coordinates = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $assignments = $this->assignmentService->getAssignmentsWithUsableSites($employee);

        $nearest = null;
        if (isset($coordinates['latitude'], $coordinates['longitude'])) {
            $nearest = $this->assignmentService->getNearestAssignedSite(
                $employee,
                (float) $coordinates['latitude'],
                (float) $coordinates['longitude']
            );
        }

        $assignment = $nearest['assignment'] ?? $assignments->first();

        return $this->success('Current site retrieved successfully', [
            'site' => $assignment?->site,
            'assignment' => $assignment,
            'sites' => $assignments->map(fn ($item) => $item->site)->filter()->values(),
            'nearest_distance' => $nearest['distance'] ?? null,
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
