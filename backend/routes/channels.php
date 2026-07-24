<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{channelId}', function ($user, $channelId) {
    $channel = \App\Models\ChatChannel::find($channelId);
    if (!$channel) return false;

    return $channel->members()->where('users.id', $user->id)->exists();
});

Broadcast::channel('private/user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
