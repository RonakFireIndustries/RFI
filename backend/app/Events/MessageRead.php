<?php

namespace App\Events;

use App\Models\ChatMessageRead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessageRead $read,
    ) {
        $this->read->load(['user', 'message']);
    }

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\Channel('chat.' . $this->read->message->chat_channel_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->read->chat_message_id,
            'user_id' => $this->read->user_id,
            'user_name' => $this->read->user->name,
            'read_at' => $this->read->read_at->toISOString(),
        ];
    }
}
