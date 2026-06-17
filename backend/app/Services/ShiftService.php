<?php

namespace App\Services;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Collection;

class ShiftService
{
    /**
     * Get all active shifts.
     */
    public function getAllShifts(): Collection
    {
        return Shift::all();
    }

    /**
     * Create a new shift.
     */
    public function createShift(array $data): Shift
    {
        return Shift::create($data);
    }

    /**
     * Update an existing shift.
     */
    public function updateShift(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift->fresh();
    }

    /**
     * Delete a shift.
     */
    public function deleteShift(Shift $shift): void
    {
        $shift->delete();
    }
}
