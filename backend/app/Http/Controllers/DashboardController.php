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
            $service = $dashboardService->forUser($request->user());

            // A dashboard type derived from role / designation / department is
            // only an identity guess; without the matching Access Control
            // grant the user must not read another team's cards and alerts.
            // Rendering the personal dashboard in place (instead of 403-ing
            // and bouncing the browser to /dashboard/my-dashboard) keeps the
            // gate intact while stopping every ungranted user from seeing
            // "Dashboard" behave like a broken redirect. getMyDashboard() is
            // entirely self-scoped, so nothing another team owns can leak.
            $data = $service->canViewDashboard()
                ? $service->getDashboard()
                : $service->getMyDashboard();

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

    /**
     * The personal "My Dashboard": self-scoped and always available to the
     * signed-in user, independent of any role dashboard permission.
     */
    public function my(Request $request, DashboardService $dashboardService)
    {
        try {
            $data = $dashboardService->forUser($request->user())->getMyDashboard();
            return new DashboardResource($data);
        } catch (\Exception $e) {
            Log::error('My dashboard failed: ' . $e->getMessage());
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

            // Same gate as index(): without it any authenticated user could
            // read another dashboard's cards and alerts by calling this
            // endpoint directly.
            if (!$dashboardService->canViewDashboard()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this dashboard.',
                    'dashboard_type' => $dashboardService->getDashboardType(),
                    'cards' => [],
                    'alerts' => [],
                ], 403);
            }

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
