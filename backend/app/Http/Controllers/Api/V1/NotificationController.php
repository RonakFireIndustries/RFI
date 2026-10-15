<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);

        $query = Notification::where('user_id', $request->user()->id);

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($request->boolean('unread')) {
            $query->where('is_read', false);
        }

        $notifications = $query->latest()->paginate($perPage);

        return $this->success('Notifications retrieved successfully', [
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]
        ]);
    }

    /**
     * The modules this user has actually received notifications under, so the
     * UI only offers filters that will return something.
     */
    public function modules(Request $request): JsonResponse
    {
        $modules = Notification::where('user_id', $request->user()->id)
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->values();

        return $this->success('Notification modules retrieved', ['modules' => $modules]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return $this->success('Unread count retrieved', ['count' => $count]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', [], 403);
        }

        $notification->update(['is_read' => true]);

        return $this->success('Notification marked as read');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->success('All notifications marked as read');
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', [], 403);
        }

        $notification->delete();

        return $this->success('Notification deleted');
    }

    public function send(Request $request): JsonResponse
    {
        $this->authorize('access-control.manage');
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:info,success,warning,error'],
            'module' => ['nullable', 'string', 'max:100'],
            'link' => ['nullable', 'string', 'max:255'],
        ]);

        // Routed through the service so the recipient's open tab is told about
        // it immediately, not on the next 30-second poll.
        $notification = NotificationService::create(
            (int) $validated['user_id'],
            $validated['title'],
            $validated['message'] ?? null,
            $validated['type'] ?? 'info',
            $validated['link'] ?? null,
            $validated['module'] ?? 'General',
        );

        if ($notification === null) {
            return $this->error('Could not send notification', [], 500);
        }

        return $this->success('Notification sent', ['notification' => $notification], [], 201);
    }
}
