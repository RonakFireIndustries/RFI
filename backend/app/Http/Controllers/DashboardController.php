<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        try {
            $data = $dashboardService->forUser($request->user())->getDashboard();
            return new DashboardResource($data);
        } catch (\Exception $e) {
            Log::error('Dashboard failed: ' . $e->getMessage());
            return response()->json([
                'dashboard_type' => 'employee',
                'cards' => [],
                'charts' => [],
                'quick_actions' => [],
                'alerts' => [],
                'widgets' => [],
            ]);
        }
    }

    public function summary(Request $request)
    {
        try {
            $dashboardService = app(DashboardService::class)->forUser($request->user());
            $data = $dashboardService->getDashboard();
            return response()->json([
                'dashboard_type' => $data['dashboard_type'],
                'cards' => $data['cards'],
                'alerts' => $data['alerts'],
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard summary failed: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}
