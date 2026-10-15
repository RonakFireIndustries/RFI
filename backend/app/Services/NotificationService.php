<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * When true, nothing is emitted. Used around bulk operations (payroll runs,
     * imports, migrations) that would otherwise emit one notification per row.
     */
    protected static bool $suspended = false;

    /**
     * Run $fn with notifications muted, then restore the previous state.
     */
    public static function suspend(callable $fn): mixed
    {
        $previous = static::$suspended;
        static::$suspended = true;

        try {
            return $fn();
        } finally {
            static::$suspended = $previous;
        }
    }

    public static function suspended(): bool
    {
        return static::$suspended;
    }

    /**
     * The full catalogue, keyed by type.
     */
    public static function types(): array
    {
        return config('notifications.types', []);
    }

    public static function definition(string $type): ?array
    {
        return static::types()[$type] ?? null;
    }

    /**
     * Emit a notification defined in config/notifications.php.
     *
     * @param string   $type      Catalogue key, e.g. "leave.requested".
     * @param array    $replace   :placeholder values, plus an optional "id" used by the link template.
     * @param int|null $actorId   The person who caused the event. Never notified about their own action.
     * @param array|null $recipients Explicit user IDs. Overrides permission resolution entirely.
     * @param string|null $link   Overrides the catalogue link.
     * @param string|null $dedupeKey Overrides the catalogue dedupe key.
     *
     * @return Collection<int, Notification>
     */
    public static function notify(
        string $type,
        array $replace = [],
        ?int $actorId = null,
        ?array $recipients = null,
        ?string $link = null,
        ?string $dedupeKey = null,
    ): Collection {
        if (static::$suspended) {
            return collect();
        }

        $def = static::definition($type);

        if ($def === null) {
            Log::warning("Notification type [{$type}] is not declared in config/notifications.php");

            return collect();
        }

        $link = $link ?? static::render($def['link'] ?? null, $replace);

        // `dedupe => true` in the catalogue means "do not stack copies of the
        // same record's alert", so the key is naturally the type plus the
        // record id. An explicit key still wins, and a `dedupe_key` template
        // is honoured for anything more custom.
        if ($dedupeKey !== null) {
            // keep the explicit key
        } elseif (! empty($def['dedupe'])) {
            $dedupeKey = $type . ':' . ($replace['id'] ?? 'global');
        } else {
            $dedupeKey = static::render($def['dedupe_key'] ?? null, $replace);
        }

        $ids = $recipients !== null
            ? array_values($recipients)
            : static::resolveRecipients($def, $replace);

        $created = collect();

        foreach (array_unique(array_filter($ids)) as $userId) {
            // Never tell someone about something they just did themselves.
            if ($actorId !== null && (int) $userId === (int) $actorId) {
                continue;
            }

            if ($dedupeKey !== null && static::alreadyNotified((int) $userId, $dedupeKey)) {
                continue;
            }

            $notification = static::create(
                (int) $userId,
                static::render($def['title'], $replace),
                static::render($def['message'] ?? null, $replace),
                $def['severity'] ?? 'info',
                $link,
                $def['module'] ?? null,
                $dedupeKey,
                $replace,
            );

            if ($notification !== null) {
                $created->push($notification);
            }
        }

        return $created;
    }

    /**
     * Persist a single notification and push it over the live channel.
     *
     * The first five parameters keep the historical signature used by the
     * original call sites; the trailing ones are additive.
     */
    public static function create(
        int $userId,
        string $title,
        ?string $message = null,
        string $type = 'info',
        ?string $link = null,
        ?string $module = null,
        ?string $dedupeKey = null,
        array $replace = [],
    ): ?Notification {
        if (static::$suspended) {
            return null;
        }

        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'module' => $module,
                'dedupe_key' => $dedupeKey,
                'data' => $replace ?: null,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            // A notification must never break the business transaction that
            // triggered it.
            Log::error('Failed to persist notification: ' . $e->getMessage());

            return null;
        }

        try {
            broadcast(new NotificationCreated($notification));
        } catch (\Throwable $e) {
            // Broadcasting is best-effort: the row is already stored and the
            // 30s polling loop will pick it up regardless.
            Log::warning('Failed to broadcast notification: ' . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Resolve recipients from the catalogue's permission rule.
     *
     * Everyone holding the permission is notified. If nobody holds it, super
     * admins are notified so an approval queue can never go silent.
     *
     * @return array<int, int>
     */
    protected static function resolveRecipients(array $def, array $replace = []): array
    {
        if (empty($def['permission'])) {
            return [];
        }

        $permission = $def['permission'];

        try {
            $ids = User::permission($permission)->pluck('id')->all();
        } catch (\Throwable $e) {
            // Permission row missing / store unavailable.
            Log::warning("Could not resolve recipients for [{$permission}]: " . $e->getMessage());
            $ids = [];
        }

        if (! empty($ids)) {
            return $ids;
        }

        try {
            return User::where('is_super_admin', true)->pluck('id')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Skip when the user already has an unread copy of the same alert, so a
     * daily scan does not pile up duplicates for a persistent condition.
     */
    public static function alreadyNotified(int $userId, string $dedupeKey): bool
    {
        try {
            return Notification::where('user_id', $userId)
                ->where('dedupe_key', $dedupeKey)
                ->where('is_read', false)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Fill :placeholders from $replace. Only known keys are substituted, so
     * literal colons in content (times like 10:30, ratios) are never mangled.
     */
    public static function render(?string $template, array $replace = []): ?string
    {
        if ($template === null) {
            return null;
        }

        $flat = [];
        foreach ($replace as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $flat[$key] = (string) ($value ?? '');
            }
        }

        if ($flat === []) {
            return $template;
        }

        return (string) preg_replace_callback(
            '/:([a-z0-9_]+)/i',
            function (array $m) use ($flat) {
                $key = strtolower($m[1]);

                return array_key_exists($key, $flat) ? $flat[$key] : $m[0];
            },
            $template
        );
    }

    /**
     * Convenience for the very common "notify the owner of this record" case.
     *
     * @return Collection<int, Notification>
     */
    public static function notifyOwner(
        string $type,
        ?int $ownerId,
        array $replace = [],
        ?int $actorId = null,
        ?string $link = null,
    ): Collection {
        if ($ownerId === null) {
            return collect();
        }

        return static::notify($type, $replace, $actorId, [$ownerId], $link);
    }

    /**
     * Best-effort "user id behind a model" helper for observers.
     */
    public static function userIdFor(?Model $model, array $candidates = ['user_id', 'created_by', 'requested_by', 'approved_by']): ?int
    {
        if ($model === null) {
            return null;
        }

        foreach ($candidates as $column) {
            if (isset($model->{$column}) && is_numeric($model->{$column})) {
                return (int) $model->{$column};
            }
        }

        return null;
    }
}
