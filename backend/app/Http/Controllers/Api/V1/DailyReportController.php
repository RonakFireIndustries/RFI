<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Services\DailyReportService;
use App\Http\Requests\StoreDailyReportRequest;
use App\Http\Requests\UpdateDailyReportRequest;
use App\Http\Resources\DailyReportResource;
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
        $this->authorize('viewAny', DailyReport::class);

        $filters = $request->only(['employee_id', 'site_id', 'date', 'start_date', 'end_date', 'status']);
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
        $this->authorize('create', DailyReport::class);

        try {
            $report = $this->dprService->createReport($request->validated());
            $report->load(['employee.designation', 'site', 'approver']);

            return $this->success('Daily report created successfully', [
                'report' => new DailyReportResource($report)
            ], 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function show(DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('view', $dailyReport);
        
        $dailyReport->load(['employee.designation', 'site', 'approver', 'histories.user']);

        return $this->success('Daily report retrieved successfully', [
            'report' => new DailyReportResource($dailyReport)
        ]);
    }

    public function update(UpdateDailyReportRequest $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('update', $dailyReport);

        try {
            $report = $this->dprService->updateReport($dailyReport, $request->validated());

            return $this->success('Daily report updated successfully', [
                'report' => new DailyReportResource($report)
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('delete', $dailyReport);

        $this->dprService->deleteReport($dailyReport);

        return $this->success('Daily report deleted successfully');
    }

    public function approve(Request $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('approve', $dailyReport);

        $request->validate(['comments' => 'nullable|string']);

        $report = $this->dprService->approve($dailyReport, $request->comments);

        return $this->success('Daily report approved successfully', [
            'report' => new DailyReportResource($report)
        ]);
    }

    public function reject(Request $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('reject', $dailyReport);

        $request->validate(['comments' => 'required|string']);

        $report = $this->dprService->reject($dailyReport, $request->comments);

        return $this->success('Daily report rejected successfully', [
            'report' => new DailyReportResource($report)
        ]);
    }

    public function rework(Request $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('rework', $dailyReport);

        $request->validate(['comments' => 'required|string']);

        $report = $this->dprService->rework($dailyReport, $request->comments);

        return $this->success('Rework requested successfully', [
            'report' => new DailyReportResource($report)
        ]);
    }
}
