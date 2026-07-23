<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Events\MessageRead as MessageReadEvent;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    use ApiResponse;

    public function channels(Request $request): JsonResponse
    {
        $user = $request->user();

        $channels = ChatChannel::whereHas('members', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->with(['site', 'members' => function ($q) {
            $q->select('users.id', 'users.name');
        }])
        ->get()
        ->map(function ($channel) use ($user) {
            $lastMessage = ChatMessage::where('chat_channel_id', $channel->id)
                ->where('created_at', '>=', $channel->members->firstWhere('id', $user->id)?->pivot->visible_from ?? '1970-01-01')
                ->with('user:id,name')
                ->latest()
                ->first();

            $unreadCount = ChatMessage::where('chat_channel_id', $channel->id)
                ->where('user_id', '!=', $user->id)
                ->where('created_at', '>=', $channel->members->firstWhere('id', $user->id)?->pivot->visible_from ?? '1970-01-01')
                ->whereDoesntHave('reads', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->count();

            return [
                'id' => $channel->id,
                'name' => $channel->name,
                'description' => $channel->description,
                'type' => $channel->type,
                'site_id' => $channel->site_id,
                'site' => $channel->site,
                'status' => $channel->status,
                'members_count' => $channel->members->count(),
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
                    'message' => $lastMessage->message,
                    'user_name' => $lastMessage->user->name,
                    'created_at' => $lastMessage->created_at->toISOString(),
                ] : null,
                'unread_count' => $unreadCount,
                'created_at' => $channel->created_at->toISOString(),
            ];
        })
        ->sortByDesc(fn ($c) => $c['last_message']['created_at'] ?? $c['created_at'])
        ->values();

        return $this->success('Channels retrieved', ['channels' => $channels]);
    }

    public function storeChannel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:site,topic'],
            'site_id' => ['required_if:type,site', 'nullable', 'exists:sites,id'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['exists:users,id'],
        ]);

        $channel = ChatChannel::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'site_id' => $validated['site_id'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $memberIds = collect($validated['member_ids']);
        if (!$memberIds->contains($request->user()->id)) {
            $memberIds->push($request->user()->id);
        }

        foreach ($memberIds as $memberId) {
            $channel->members()->attach($memberId, [
                'visible_from' => now(),
            ]);
        }

        $channel->load(['site', 'members']);

        return $this->success('Channel created', ['channel' => $channel], [], 201);
    }

    public function addMembers(Request $request, ChatChannel $channel): JsonResponse
    {
        $user = $request->user();
        if (!$channel->members()->where('users.id', $user->id)->exists()) {
            return $this->error('You are not a member of this channel', [], 403);
        }

        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['exists:users,id'],
        ]);

        foreach ($validated['member_ids'] as $memberId) {
            $channel->members()->syncWithoutDetaching([
                $memberId => ['visible_from' => now()],
            ]);
        }

        $channel->load('members');

        return $this->success('Members added', ['channel' => $channel]);
    }

    public function removeMember(Request $request, ChatChannel $channel, int $userId): JsonResponse
    {
        $user = $request->user();
        if (!$channel->members()->where('users.id', $user->id)->exists()) {
            return $this->error('You are not a member of this channel', [], 403);
        }

        if (!$channel->members()->where('users.id', $userId)->exists()) {
            return $this->error('User is not a member of this channel', [], 404);
        }

        $channel->members()->detach($userId);
        return $this->success('Member removed');
    }

    public function messages(Request $request, ChatChannel $channel): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->input('per_page', 50);

        $visibleFrom = $channel->members()->where('users.id', $user->id)->first()?->pivot->visible_from ?? '1970-01-01';

        $beforeId = $request->input('before_id');
        $query = ChatMessage::where('chat_channel_id', $channel->id)
            ->where('created_at', '>=', $visibleFrom)
            ->with(['user:id,name', 'replyTo' => function ($q) {
                $q->select('id', 'chat_channel_id', 'user_id', 'message', 'type', 'created_at');
                $q->with('user:id,name');
            }, 'reads' => function ($q) {
                $q->select('chat_message_reads.chat_message_id', 'chat_message_reads.user_id', 'chat_message_reads.read_at', 'users.name');
                $q->join('users', 'users.id', '=', 'chat_message_reads.user_id');
            }]);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->latest()->paginate($perPage);

        $messagesData = $messages->reverse()->values()->map(function ($msg) use ($user) {
            return [
                'id' => $msg->id,
                'chat_channel_id' => $msg->chat_channel_id,
                'user_id' => $msg->user_id,
                'message' => $msg->message,
                'type' => $msg->type,
                'file_path' => $msg->file_path,
                'file_name' => $msg->file_name,
                'reply_to_id' => $msg->reply_to_id,
                'reply_to' => $msg->replyTo ? [
                    'id' => $msg->replyTo->id,
                    'message' => $msg->replyTo->message,
                    'user' => $msg->replyTo->user ? ['id' => $msg->replyTo->user->id, 'name' => $msg->replyTo->user->name] : null,
                ] : null,
                'user' => ['id' => $msg->user->id, 'name' => $msg->user->name],
                'reads' => $msg->reads->map(fn ($r) => ['user_id' => $r->user_id, 'user_name' => $r->name, 'read_at' => $r->read_at->toISOString()]),
                'is_mine' => $msg->user_id === $user->id,
                'created_at' => $msg->created_at->toISOString(),
            ];
        });

        return $this->success('Messages retrieved', [
            'messages' => $messagesData,
            'has_more' => $messages->hasMorePages(),
        ]);
    }

    public function sendMessage(Request $request, ChatChannel $channel): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'reply_to_id' => ['nullable', 'exists:chat_messages,id'],
            'file' => ['required_without:message', 'file', 'max:10240'],
        ]);

        $data = [
            'chat_channel_id' => $channel->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'] ?? null,
            'type' => 'text',
            'reply_to_id' => $validated['reply_to_id'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat-files', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['type'] = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file';
        }

        $message = ChatMessage::create($data);
        $message->load(['user', 'replyTo.user', 'reads']);

        broadcast(new MessageSent($message));

        return $this->success('Message sent', ['message' => $this->formatMessage($message, $request->user())], [], 201);
    }

    public function markRead(Request $request, ChatChannel $channel): JsonResponse
    {
        $user = $request->user();

        $unreadMessages = ChatMessage::where('chat_channel_id', $channel->id)
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get();

        foreach ($unreadMessages as $msg) {
            ChatMessageRead::create([
                'chat_message_id' => $msg->id,
                'user_id' => $user->id,
                'read_at' => now(),
            ]);
            broadcast(new MessageReadEvent(
                ChatMessageRead::where('chat_message_id', $msg->id)->where('user_id', $user->id)->first()
            ));
        }

        return $this->success('Marked as read');
    }

    public function typing(Request $request, ChatChannel $channel): JsonResponse
    {
        broadcast(new UserTyping(
            $request->user()->id,
            $request->user()->name,
            $channel->id,
        ));

        return response()->json(['status' => 'ok']);
    }

    public function members(Request $request, ChatChannel $channel): JsonResponse
    {
        $members = $channel->members()->select('users.id', 'users.name')->get();
        return $this->success('Members retrieved', ['members' => $members]);
    }

    private function formatMessage(ChatMessage $msg, $user): array
    {
        return [
            'id' => $msg->id,
            'chat_channel_id' => $msg->chat_channel_id,
            'user_id' => $msg->user_id,
            'message' => $msg->message,
            'type' => $msg->type,
            'file_path' => $msg->file_path,
            'file_name' => $msg->file_name,
            'reply_to_id' => $msg->reply_to_id,
            'reply_to' => $msg->replyTo ? [
                'id' => $msg->replyTo->id,
                'message' => $msg->replyTo->message,
                'user' => $msg->replyTo->user ? ['id' => $msg->replyTo->user->id, 'name' => $msg->replyTo->user->name] : null,
            ] : null,
            'user' => ['id' => $msg->user->id, 'name' => $msg->user->name],
            'reads' => $msg->reads->map(fn ($r) => ['user_id' => $r->user_id, 'user_name' => $r->name, 'read_at' => $r->read_at->toISOString()]),
            'is_mine' => $msg->user_id === $user->id,
            'created_at' => $msg->created_at->toISOString(),
        ];
    }
}
