<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;

class NotificationService
{
    public static function create(
        int $userId,
        string $title,
        ?string $message = null,
        string $type = 'info',
        ?string $link = null,
    ): Notification {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => false,
        ]);

        broadcast(new NotificationCreated($notification));

        return $notification;
    }
}
