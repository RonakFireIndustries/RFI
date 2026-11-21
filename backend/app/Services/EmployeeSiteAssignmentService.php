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
    protected GeoLocationService $geoLocation;

    public function __construct(GeoLocationService $geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    /**
     * Get active site assignments for an employee.
     */
    public function getEmployeeSites(Employee $employee)
    {
        return $employee->employeeSites()->with('site')->get();
    }

    /**
     * Get the employee's assignments whose site is active and has GPS
     * coordinates. These are the only assignments that can be used when
     * resolving a physical location (check-in, dashboard radius, etc.).
     */
    public function getAssignmentsWithUsableSites(Employee $employee)
    {
        return $employee->employeeSites()
            ->with('site')
            ->whereHas('site', function ($query) {
                $query->where('status', 'Active')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            })
            ->orderBy('employee_sites.id')
            ->get();
    }

    /**
     * Resolve the closest of the employee's assigned sites to the supplied
     * coordinates. An employee assigned to several sites can therefore check
     in from any of them instead of being measured against an arbitrary one.
     *
     * @return array{site: Site, assignment: EmployeeSite, distance: float}|null
     */
    public function getNearestAssignedSite(Employee $employee, float $latitude, float $longitude): ?array
    {
        $assignments = $this->getAssignmentsWithUsableSites($employee);

        $nearest = $this->geoLocation->nearestSite(
            $latitude,
            $longitude,
            $assignments->map(fn ($assignment) => $assignment->site)->filter()->values()
        );

        if (!$nearest) {
            return null;
        }

        return [
            'site' => $nearest['site'],
            'assignment' => $assignments->first(
                fn ($assignment) => (int) $assignment->site_id === (int) $nearest['site']->id
            ),
            'distance' => $nearest['distance'],
        ];
    }

    /**
     * IDs of the active sites the employee is assigned to.
     *
     * @return array<int, int>
     */
    public function getAssignedSiteIds(Employee $employee): array
    {
        return $employee->employeeSites()
            ->whereHas('site', fn ($query) => $query->where('status', 'Active'))
            ->pluck('site_id')
            ->map(fn ($id) => (int) $id)
            ->all();
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
     * Assign an employee to several sites in one action.
     *
     * Existing assignments are left untouched, so re-submitting a site the
     * employee already has is a no-op rather than an error.
     */
    public function assignEmployeeToSites(Employee $employee, array $siteIds, ?string $role = null, ?string $remarks = null)
    {
        return DB::transaction(function () use ($employee, $siteIds, $role, $remarks) {
            $siteIds = array_values(array_unique(array_map('intval', $siteIds)));

            $existingIds = EmployeeSite::where('employee_id', $employee->id)
                ->whereIn('site_id', $siteIds)
                ->pluck('site_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $newIds = array_values(array_diff($siteIds, $existingIds));
            $assignments = collect();

            foreach ($newIds as $siteId) {
                $assignment = EmployeeSite::create([
                    'employee_id' => $employee->id,
                    'site_id' => $siteId,
                    'assigned_at' => now(),
                    'role' => $role,
                ]);

                EmployeeSiteHistory::create([
                    'employee_id' => $employee->id,
                    'assigned_by_id' => Auth::id(),
                    'previous_site_id' => null,
                    'new_site_id' => $siteId,
                    'assigned_at' => now(),
                    'remarks' => $remarks ?? 'Assigned to site',
                ]);

                $assignments->push($assignment);
            }

            return $assignments;
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
