<?php

namespace App\Support;

/**
 * Single source of truth for resource validation rules.
 *
 * Each method describes a resource's fields in a human-readable, JSON-friendly
 * format. From it we derive:
 *   - Laravel validation rules (for FormRequests / store & update)
 *   - a JSON schema exported for the frontend (validationSchemas.json)
 *
 * Extend as new resources are onboarded; keep this class the ONLY place where
 * field constraints live so Store*Request / Update*Request never drift.
 */
class ValidationSchemas
{
    public static function dailyReport(): array
    {
        return [
            'site_id'          => ['type' => 'string', 'exists' => 'sites,id'],
            'date'             => ['type' => 'date', 'required' => true],
            'work_description' => ['type' => 'string', 'required' => true],
            'tasks_completed'  => ['type' => 'string'],
            'hours_worked'     => ['type' => 'number', 'required' => true, 'min' => 0, 'max' => 24],
            'issues_faced'     => ['type' => 'string'],
            'materials_used'   => ['type' => 'string'],
            'equipment_used'   => ['type' => 'string'],
            'status'           => ['in' => ['Draft', 'Submitted']],
        ];
    }

    public static function employee(): array
    {
        return [];
    }

    public static function leave(): array
    {
        return [];
    }

    public static function product(): array
    {
        return [];
    }

    public static function site(): array
    {
        return [];
    }

    /**
     * Convert a structured schema into Laravel validation rule strings.
     *
     * For store requests, required fields stay required. For updates they
     * become "sometimes" so partial payloads validate correctly.
     *
     * @return array<string, string>
     */
    public static function toLaravelRules(array $schema, bool $forUpdate = false): array
    {
        $rules = [];

        foreach ($schema as $field => $def) {
            $parts = [];

            if (!empty($def['required'])) {
                $parts[] = $forUpdate ? 'sometimes' : 'required';
            } else {
                $parts[] = 'nullable';
            }

            $typeRule = match ($def['type'] ?? 'string') {
                'number' => 'numeric',
                'integer' => 'integer',
                'date' => 'date',
                'boolean' => 'boolean',
                'array' => 'array',
                default => 'string',
            };
            $parts[] = $typeRule;

            if (array_key_exists('min', $def)) {
                $parts[] = 'min:' . $def['min'];
            }

            if (array_key_exists('max', $def)) {
                $parts[] = 'max:' . $def['max'];
            }

            if (array_key_exists('in', $def)) {
                $parts[] = 'in:' . implode(',', $def['in']);
            }

            if (!empty($def['exists'])) {
                $parts[] = 'exists:' . $def['exists'];
            }

            if (!empty($def['unique'])) {
                $parts[] = 'unique:' . $def['unique'];
            }

            $rules[$field] = implode('|', $parts);
        }

        return $rules;
    }

    /**
     * Produce a JSON-serializable representation for the frontend.
     */
    public static function toJsonSchema(array $schema): array
    {
        return $schema;
    }

    /**
     * All registered schemas, keyed by resource name.
     *
     * @return array<string, array>
     */
    public static function export(): array
    {
        return [
            'dailyReport' => static::toJsonSchema(static::dailyReport()),
            'employee' => static::toJsonSchema(static::employee()),
            'leave' => static::toJsonSchema(static::leave()),
            'product' => static::toJsonSchema(static::product()),
            'site' => static::toJsonSchema(static::site()),
        ];
    }
}