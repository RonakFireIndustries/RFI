<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Services\DailyReportService;
use App\Http\Requests\StoreDailyReportRequest;
use App\Http\Requests\UpdateDailyReportRequest;
use App\Http\Resources\DailyReportResource;
use App\Support\Access;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class DailyReportController extends Controller
{
    use ApiResponse;

    protected DailyReportService $dprService;

    public function __construct(DailyReportService $dprService)
    {
        $this->dprService = $dprService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['employee_id', 'site_id', 'date', 'start_date', 'end_date', 'status']);

        $user = $request->user();
        $employee = $user->employee;

        $hasGlobalView = Access::isSuperAdmin($user) || (
            $user->hasAnyPermission(['daily-reports.view'])
            && $user->hasAnyPermission(['daily-reports.approve', 'daily-reports.reject'])
        );

        if (!$hasGlobalView && !$user->hasAnyPermission('daily-report.report.view')) {
            // Basic employee: own reports only. Reporting managers: own + direct subordinates.
            $visibleIds = collect([$employee?->id])
                ->merge($employee ? $employee->subordinates()->pluck('employees.id') : [])
                ->filter()
                ->unique()
                ->values();
            $filters['employee_id'] = $visibleIds->isEmpty() ? -1 : $visibleIds->all();
        }

        $perPage = (int) $request->input('per_page', 15);

        $reports = $this->dprService->getReports($filters, $perPage);

        return $this->success('Daily reports retrieved successfully', [
            'reports' => DailyReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ]
        ]);
    }

    public function store(StoreDailyReportRequest $request): JsonResponse
    {
        try {
            $this->authorize('daily-reports.create');

            $employee = $request->user()->employee
                ?? throw new Exception('No employee profile linked to your account.');

            $data = $request->validated();
            $data['employee_id'] = $employee->id;

            // Employees may only file a report against the site currently
            // assigned to them. Global managers (super admins / sites.view)
            // may pick any site when back-filling.
            $assignedSiteId = $employee->employeeSites()
                ->orderBy('id')
                ->first()
                ?->site_id;

            $isGlobalManager = Access::isSuperAdmin($request->user())
                || $request->user()->can('sites.view');

            if (!$isGlobalManager) {
                if (!$assignedSiteId) {
                    throw new Exception('No site is currently assigned to you. Please contact your administrator before submitting a daily report.');
                }
                if (!empty($data['site_id']) && (int) $data['site_id'] !== (int) $assignedSiteId) {
                    throw new Exception('Daily reports can only be submitted for the site currently assigned to you.');
                }
                $data['site_id'] = $assignedSiteId;
            } elseif (!$data['site_id']) {
                $data['site_id'] = $assignedSiteId;
            }

            $report = $this->dprService->createReport($data);
            $report->load(['employee.designation', 'site', 'approver']);

            return $this->success('Daily report created successfully', [
                'report' => new DailyReportResource($report)
            ], 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function show(DailyReport $dailyReport): JsonResponse
    {
        $user = request()->user();
        $employee = $user->employee;

        $visible = Access::isSuperAdmin($user)
            || $user->hasAnyPermission(['daily-reports.approve', 'daily-reports.reject'])
            || $dailyReport->employee_id === $employee?->id
            || $dailyReport->employee?->reporting_manager_id === $employee?->id;

        abort_unless($visible, 403, 'You are not allowed to view this report.');

        $dailyReport->load(['employee.designation', 'site', 'approver', 'histories.user']);

        return $this->success('Daily report retrieved successfully', [
            'report' => new DailyReportResource($dailyReport)
        ]);
    }

    public function update(UpdateDailyReportRequest $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('daily-reports.update');

        try {
            $data = $request->validated();

            $report = $this->dprService->updateReport($dailyReport, $data);

            return $this->success('Daily report updated successfully', [
                'report' => new DailyReportResource($report)
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function destroy(DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('daily-reports.delete');

        try {
            $this->dprService->deleteReport($dailyReport);

            return $this->success('Daily report deleted successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function approve(DailyReport $dailyReport): JsonResponse
    {
        abort_unless($dailyReport->canBeReviewedBy(request()->user()), 403, 'Only the reporting manager can review this report.');

        try {
            $report = $this->dprService->approve($dailyReport);

            return $this->success('Daily report approved successfully', [
                'report' => new DailyReportResource($report)
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function reject(DailyReport $dailyReport): JsonResponse
    {
        abort_unless($dailyReport->canBeReviewedBy(request()->user()), 403, 'Only the reporting manager can review this report.');

        try {
            $report = $this->dprService->reject($dailyReport, (string) request('comments', ''));

            return $this->success('Daily report rejected successfully', [
                'report' => new DailyReportResource($report)
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function rework(DailyReport $dailyReport): JsonResponse
    {
        abort_unless($dailyReport->canBeReviewedBy(request()->user()), 403, 'Only the reporting manager can review this report.');

        try {
            $report = $this->dprService->rework($dailyReport, (string) request('comments', ''));

            return $this->success('Daily report sent for rework successfully', [
                'report' => new DailyReportResource($report)
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }
}
