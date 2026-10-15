<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared helpers for the notification observers.
 *
 * Every observer reacts to Eloquent model events rather than controller calls,
 * so a status change is caught no matter where it came from - a controller, a
 * form request, a service class, a seeder or an artisan command.
 */
abstract class NotificationObserver
{
    /**
     * True when the named attribute was actually written by this save.
     * Reading the attribute alone is not enough: an update that leaves it
     * unchanged must not fire a notification.
     */
    protected static function changed(Model $model, string $field): bool
    {
        return $model->wasChanged($field);
    }

    /**
     * True when the attribute both changed and landed on one of $to.
     *
     * During the `updated` event getOriginal() still returns the pre-save
     * value, so it is safe to read the previous state here.
     */
    protected static function transitioned(Model $model, string $field, array|string $to): bool
    {
        if (! static::changed($model, $field)) {
            return false;
        }

        $value = $model->getAttribute($field);

        return is_array($to)
            ? in_array($value, $to, true)
            : $value === $to;
    }

    /**
     * The value the attribute held before this save.
     */
    protected static function previous(Model $model, string $field): mixed
    {
        return $model->getOriginal($field);
    }

    /**
     * First non-null numeric user id found among the given columns. Observers
     * pass their module's ownership columns in order of preference.
     */
    protected static function actorId(Model $model, array $columns): ?int
    {
        foreach ($columns as $column) {
            $value = $model->getAttribute($column);

            if ($value !== null && is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * Who is signed in right now. Model events also fire from console and
     * queue contexts where there is no session, hence the defensive read.
     */
    protected static function currentActor(): ?int
    {
        try {
            $id = auth()->id();

            return $id !== null ? (int) $id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Read a related model's attribute defensively. Relations are not always
     * loaded on a model instance straight out of a mass update.
     */
    protected static function relationAttribute(Model $model, string $relation, string $attribute, ?string $fallback = null): ?string
    {
        try {
            $value = $model->getAttribute($relation)?->getAttribute($attribute);

            return $value !== null ? (string) $value : $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    /**
     * Date columns arrive as strings, Carbon or null depending on how the row
     * was hydrated; normalise for message templates.
     */
    protected static function formatDate(mixed $value, string $format = 'd M Y'): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    protected static function money(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 2);
    }

    /**
     * The user behind an `employee` ownership relation. Leave and daily
     * reports are owned by the employee row, not directly by a user, so the
     * relation has to be resolved - reading `employee_id` alone only yields
     * the employee's own primary key.
     */
    protected static function userForEmployee(Model $model, string $relation = 'employee'): ?int
    {
        try {
            $userId = $model->getAttribute($relation)?->getAttribute('user_id');

            return $userId !== null ? (int) $userId : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
