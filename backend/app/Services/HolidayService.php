<?php

namespace App\Services;

use App\Models\Holiday;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    /**
     * Get the company holiday calendar, filtered and paginated.
     *
     * Supported filters: year, type, status, search, from, to.
     */
    public function getHolidays(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Holiday::query();

        if (!empty($filters['year'])) {
            $query->forYear((int) $filters['year']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from'])) {
            $query->where('date', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('date', '<=', $filters['to']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('date')->paginate($perPage);
    }

    /**
     * The years that have a holiday on the calendar, newest first.
     *
     * The current year is always included so the calendar of a year that has
     * not been announced yet can still be opened (and filled in).
     */
    public function getYears(): array
    {
        $driver = DB::getDriverName();
        $yearExpr = $driver === 'sqlite' ? "strftime('%Y', date)" : 'YEAR(date)';

        $years = Holiday::query()
            ->selectRaw("{$yearExpr} as year")
            ->distinct()
            ->orderByDesc('year')
            ->get()
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->all();

        $currentYear = (int) now()->year;

        if (!in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
        }

        return array_values($years);
    }

    /**
     * Create a new holiday.
     */
    public function createHoliday(array $data): Holiday
    {
        $data['status'] = $data['status'] ?? Holiday::STATUS_ACTIVE;

        return Holiday::create($data);
    }

    /**
     * Update an existing holiday.
     */
    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);

        return $holiday->fresh();
    }

    /**
     * Delete a holiday.
     */
    public function deleteHoliday(Holiday $holiday): void
    {
        $holiday->delete();
    }
}
