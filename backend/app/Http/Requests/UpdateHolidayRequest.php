<?php

namespace App\Http\Requests;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the payload before validation.
     *
     * Same alias folding as the store request - see StoreHolidayRequest.
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
        $holidayId = $this->route('holiday');
        $holidayId = is_object($holidayId) ? $holidayId->getKey() : $holidayId;

        return [
            'name' => 'required|string|max:255',
            // The holiday being edited keeps its own date; only a different
            // date has to be free.
            'date' => [
                'required',
                'date',
                Rule::unique('holidays', 'date')->ignore($holidayId),
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
