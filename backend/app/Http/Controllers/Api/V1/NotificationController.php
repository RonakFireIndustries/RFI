<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);

        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

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
        $this->authorize('manage_users');
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:info,success,warning,error'],
            'link' => ['nullable', 'string', 'max:255'],
        ]);

        $notification = Notification::create($validated);

        return $this->success('Notification sent', ['notification' => $notification], [], 201);
    }
}
