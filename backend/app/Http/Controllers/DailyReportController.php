<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Http\Resources\DailyReportResource;
use App\Http\Requests\StoreDailyReportRequest;
use App\Http\Requests\UpdateDailyReportRequest;

class DailyReportController extends Controller
{
    public function index()
    {
        $reports = DailyReport::with(['employee.user', 'site'])->latest()->get();
        return response()->json(['success' => true, 'message' => 'Success', 'data' => DailyReportResource::collection($reports)], 200);
    }

    public function store(StoreDailyReportRequest $request)
    {
        $data = $request->validated();

        $report = DailyReport::create($data);
        return response()->json(['success' => true, 'message' => 'Report created', 'data' => new DailyReportResource($report)], 201);
    }

    public function show(DailyReport $dailyReport)
    {
        return response()->json(['success' => true, 'message' => 'Success', 'data' => new DailyReportResource($dailyReport->load(['employee.user','site']))], 200);
    }

    public function destroy(DailyReport $dailyReport)
    {
        $dailyReport->delete();
        return response()->json(['success' => true, 'message' => 'Deleted', 'data' => null], 200);
    }
}
