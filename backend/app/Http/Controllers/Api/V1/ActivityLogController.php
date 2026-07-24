<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('building.view');

        $query = ActivityLog::with('user');

        if ($request->filled('loggable_type') && $request->filled('loggable_id')) {
            $query->where('loggable_type', $request->loggable_type)
                  ->where('loggable_id', $request->loggable_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $perPage = (int) $request->input('per_page', 20);
        $logs = $query->latest()->paginate($perPage);

        return $this->success('Activity logs retrieved', [
            'activity_logs' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
