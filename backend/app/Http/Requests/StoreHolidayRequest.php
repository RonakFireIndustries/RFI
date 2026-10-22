<?php

namespace App\Http\Requests;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the payload before validation.
     *
     * The type column is free text in the database, so a caller can post
     * "Public" or "public holiday" as well as the canonical label. Fold those
     * onto the canonical value first, then validate against the real list -
     * otherwise a valid alias would be rejected as an unknown type.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'type' => $this->normaliseType($this->input('type')),
            'status' => $this->normaliseStatus($this->input('status')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // One holiday per date: the company is closed once on a given day.
            'date' => [
                'required',
                'date',
                Rule::unique('holidays', 'date'),
            ],
            'type' => ['required', Rule::in(Holiday::TYPES)],
            'status' => ['nullable', Rule::in(Holiday::STATUSES)],
            'description' => 'nullable|string|max:1000',
        ];
    }

    private function normaliseType(mixed $type): mixed
    {
        if (!is_string($type) || $type === '') {
            return $type;
        }

        return Holiday::TYPE_ALIASES[strtolower($type)] ?? $type;
    }

    private function normaliseStatus(mixed $status): mixed
    {
        if (!is_string($status) || $status === '') {
            return $status;
        }

        return match (strtolower($status)) {
            'active' => Holiday::STATUS_ACTIVE,
            'inactive', 'disabled', 'cancelled' => Holiday::STATUS_INACTIVE,
            default => $status,
        };
    }
}
