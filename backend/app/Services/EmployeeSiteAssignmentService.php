<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSite;
use App\Models\EmployeeSiteHistory;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmployeeSiteAssignmentService
{
    /**
     * Get active site assignments for an employee.
     */
    public function getEmployeeSites(Employee $employee)
    {
        return $employee->employeeSites()->with('site')->get();
    }

    /**
     * Get site assignment history for an employee.
     */
    public function getEmployeeSiteHistory(Employee $employee)
    {
        return $employee->siteHistories()
            ->with(['previousSite', 'newSite', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Assign employee to a site.
     */
    public function assignEmployeeToSite(Employee $employee, int $siteId, ?string $role = null, ?string $remarks = null): EmployeeSite
    {
        return DB::transaction(function () use ($employee, $siteId, $role, $remarks) {
            // Check if already assigned
            $existing = EmployeeSite::where('employee_id', $employee->id)
                ->where('site_id', $siteId)
                ->first();

            if ($existing) {
                return $existing;
            }

            // Create assignment
            $assignment = EmployeeSite::create([
                'employee_id' => $employee->id,
                'site_id' => $siteId,
                'assigned_at' => now(),
                'role' => $role,
            ]);

            // Log history
            EmployeeSiteHistory::create([
                'employee_id' => $employee->id,
                'assigned_by_id' => Auth::id(),
                'previous_site_id' => null,
                'new_site_id' => $siteId,
                'assigned_at' => now(),
                'remarks' => $remarks ?? 'Assigned to site',
            ]);

            return $assignment;
        });
    }

    /**
     * Transfer employee from one site to another.
     */
    public function transferEmployee(Employee $employee, int $currentSiteId, int $newSiteId, ?string $role = null, ?string $remarks = null): EmployeeSite
    {
        return DB::transaction(function () use ($employee, $currentSiteId, $newSiteId, $role, $remarks) {
            // Remove from current site
            EmployeeSite::where('employee_id', $employee->id)
                ->where('site_id', $currentSiteId)
                ->delete();

            // Create new assignment
            $assignment = EmployeeSite::create([
                'employee_id' => $employee->id,
                'site_id' => $newSiteId,
                'assigned_at' => now(),
                'role' => $role,
            ]);

            // Log history
            EmployeeSiteHistory::create([
                'employee_id' => $employee->id,
                'assigned_by_id' => Auth::id(),
                'previous_site_id' => $currentSiteId,
                'new_site_id' => $newSiteId,
                'assigned_at' => now(),
                'transfer_date' => now(),
                'remarks' => $remarks ?? 'Transferred site',
            ]);

            return $assignment;
        });
    }

    /**
     * Remove employee from a site.
     */
    public function removeEmployeeFromSite(Employee $employee, int $siteId, ?string $remarks = null): void
    {
        DB::transaction(function () use ($employee, $siteId, $remarks) {
            EmployeeSite::where('employee_id', $employee->id)
                ->where('site_id', $siteId)
                ->delete();

            // Log history
            EmployeeSiteHistory::create([
                'employee_id' => $employee->id,
                'assigned_by_id' => Auth::id(),
                'previous_site_id' => $siteId,
                'new_site_id' => null,
                'assigned_at' => now(),
                'remarks' => $remarks ?? 'Removed from site',
            ]);
        });
    }

    /**
     * Get employees currently assigned to a site.
     */
    public function getSiteEmployees(Site $site)
    {
        return Employee::with(['designation', 'department'])
            ->whereHas('employeeSites', function ($query) use ($site) {
                $query->where('site_id', $site->id);
            })->get();
    }
}
