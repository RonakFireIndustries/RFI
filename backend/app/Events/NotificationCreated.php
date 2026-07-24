<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Notification $notification,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\Channel('private/user.' . $this->notification->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'link' => $this->notification->link,
            'is_read' => $this->notification->is_read,
            'created_at' => $this->notification->created_at->toISOString(),
        ];
    }
}
