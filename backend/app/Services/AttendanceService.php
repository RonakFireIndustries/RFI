<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Get attendance records with filters.
     */
    public function getAttendances(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Attendance::with(['employee', 'site', 'shift']);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }

        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    /**
     * Store a new attendance record (manual entry).
     */
    public function createAttendance(array $data): Attendance
    {
        // Calculate working hours if both check_in and check_out are provided
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $this->calculateHours($data);
        }

        return Attendance::create($data);
    }

    /**
     * Update an attendance record.
     */
    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        // Re-calculate working hours if check_in or check_out is updated
        $checkIn = $data['check_in'] ?? $attendance->check_in;
        $checkOut = $data['check_out'] ?? $attendance->check_out;

        if ($checkIn && $checkOut) {
            $data['check_in'] = $checkIn;
            $data['check_out'] = $checkOut;
            $this->calculateHours($data);
        }

        $attendance->update($data);
        return $attendance->fresh(['employee', 'site', 'shift']);
    }

    /**
     * Delete an attendance record.
     */
    public function deleteAttendance(Attendance $attendance): void
    {
        $attendance->delete();
    }

    /**
     * Calculate working hours and overtime.
     */
    protected function calculateHours(array &$data): void
    {
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $diffInHours = $checkIn->diffInMinutes($checkOut) / 60;
        $data['working_hours'] = round($diffInHours, 2);

        // Basic overtime calculation (assuming 8 hours is standard)
        // Advanced logic would use $data['shift_id']
        if ($data['working_hours'] > 8) {
            $data['overtime_hours'] = $data['working_hours'] - 8;
            $data['overtime'] = true;
        } else {
            $data['overtime_hours'] = 0;
            $data['overtime'] = false;
        }
    }
}
