<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message,
    ) {
        $this->message->load(['user', 'replyTo.user', 'reads']);
    }

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\Channel('chat.' . $this->message->chat_channel_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'chat_channel_id' => $this->message->chat_channel_id,
            'user_id' => $this->message->user_id,
            'message' => $this->message->message,
            'type' => $this->message->type,
            'file_path' => $this->message->file_path,
            'file_name' => $this->message->file_name,
            'reply_to_id' => $this->message->reply_to_id,
            'reply_to' => $this->message->replyTo ? [
                'id' => $this->message->replyTo->id,
                'message' => $this->message->replyTo->message,
                'user' => ['id' => $this->message->replyTo->user->id, 'name' => $this->message->replyTo->user->name],
            ] : null,
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
            ],
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
