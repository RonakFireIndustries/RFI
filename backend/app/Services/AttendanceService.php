<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Site;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    protected GeoLocationService $geoLocation;

    public function __construct(GeoLocationService $geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

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

        if (!empty($filters['location_verified'])) {
            $query->where('location_verified', $filters['location_verified']);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    public function createAttendance(array $data): Attendance
    {
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $this->calculateHours($data);
        }

        return Attendance::create($data);
    }

    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
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

    public function deleteAttendance(Attendance $attendance): void
    {
        $attendance->delete();
    }

    public function processCheckIn(array $data, Employee $employee, Site $site): array
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            return [
                'success' => false,
                'message' => 'Already checked in today',
                'status' => 400,
            ];
        }

        $distance = $this->geoLocation->calculateDistance(
            $site->latitude,
            $site->longitude,
            $data['latitude'],
            $data['longitude']
        );

        $allowedRadius = $site->allowed_radius ?? 100;

        if ($site->geo_fencing_enabled) {
            if ($distance > $allowedRadius) {
                return [
                    'success' => false,
                    'message' => "You are " . round($distance) . "m away from the assigned site. Move within {$allowedRadius}m radius to check in.",
                    'data' => [
                        'distance' => round($distance, 2),
                        'allowed_radius' => $allowedRadius,
                    ],
                    'status' => 403,
                ];
            }

            $accuracy = $data['accuracy'] ?? null;
            if (!$this->geoLocation->isAccuracyAcceptable($accuracy)) {
                return [
                    'success' => false,
                    'message' => 'GPS accuracy is too low (' . round($accuracy) . 'm). Move to an open area with better GPS signal.',
                    'data' => [
                        'accuracy' => $accuracy,
                        'max_accuracy' => $this->geoLocation->getMaxAccuracyThreshold(),
                    ],
                    'status' => 403,
                ];
            }
        } else {
            $accuracy = $data['accuracy'] ?? null;
        }

        $locationVerified = $this->geoLocation->isWithinRadius($distance, $allowedRadius);

        $attendanceData = [
            'employee_id' => $employee->id,
            'site_id' => $site->id,
            'date' => $today,
            'check_in' => $now,
            'status' => 'Present',
            'checkin_latitude' => $data['latitude'],
            'checkin_longitude' => $data['longitude'],
            'checkin_distance' => round($distance, 2),
            'location_verified' => $locationVerified,
            'accuracy' => $accuracy,
            'device_info' => $data['device_info'] ?? request()->userAgent(),
            'browser_info' => request()->header('User-Agent'),
            'ip_address' => request()->ip(),
        ];

        if (!empty($data['shift_id'])) {
            $attendanceData['shift_id'] = $data['shift_id'];
        }

        $attendance = Attendance::create($attendanceData);

        return [
            'success' => true,
            'message' => 'Checked in successfully',
            'data' => [
                'attendance' => $attendance,
                'distance' => round($distance, 2),
                'allowed_radius' => $allowedRadius,
                'location_verified' => $locationVerified,
            ],
            'status' => 200,
        ];
    }

    public function processCheckOut(array $data, Employee $employee, Site $site): array
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return [
                'success' => false,
                'message' => 'Must check in first',
                'status' => 400,
            ];
        }

        if ($attendance->check_out) {
            return [
                'success' => false,
                'message' => 'Already checked out today',
                'status' => 400,
            ];
        }

        $distance = $this->geoLocation->calculateDistance(
            $site->latitude,
            $site->longitude,
            $data['latitude'],
            $data['longitude']
        );

        $allowedRadius = $site->allowed_radius ?? 100;

        if ($site->geo_fencing_enabled) {
            if ($distance > $allowedRadius) {
                return [
                    'success' => false,
                    'message' => "You are " . round($distance) . "m away from the assigned site. Move within {$allowedRadius}m radius to check out.",
                    'data' => [
                        'distance' => round($distance, 2),
                        'allowed_radius' => $allowedRadius,
                    ],
                    'status' => 403,
                ];
            }

            $accuracy = $data['accuracy'] ?? null;
            if (!$this->geoLocation->isAccuracyAcceptable($accuracy)) {
                return [
                    'success' => false,
                    'message' => 'GPS accuracy is too low (' . round($accuracy) . 'm). Move to an open area with better GPS signal.',
                    'data' => [
                        'accuracy' => $accuracy,
                        'max_accuracy' => $this->geoLocation->getMaxAccuracyThreshold(),
                    ],
                    'status' => 403,
                ];
            }
        } else {
            $accuracy = $data['accuracy'] ?? null;
        }

        $locationVerified = $this->geoLocation->isWithinRadius($distance, $allowedRadius);

        $updateData = [
            'check_out' => $now,
            'checkout_latitude' => $data['latitude'],
            'checkout_longitude' => $data['longitude'],
            'checkout_distance' => round($distance, 2),
            'location_verified' => $locationVerified,
            'device_info' => $data['device_info'] ?? request()->userAgent(),
            'browser_info' => request()->header('User-Agent'),
        ];

        $this->updateAttendance($attendance, $updateData);

        return [
            'success' => true,
            'message' => 'Checked out successfully',
            'data' => [
                'attendance' => $attendance->fresh(),
                'distance' => round($distance, 2),
                'allowed_radius' => $allowedRadius,
                'location_verified' => $locationVerified,
            ],
            'status' => 200,
        ];
    }

    public function validateCheckInPrerequisites(Employee $employee): array
    {
        if (!$employee->user) {
            return [
                'success' => false,
                'message' => 'Your account is not active. Contact your administrator.',
                'status' => 403,
            ];
        }

        $activeSite = $employee->employeeSites()
            ->whereHas('site', fn($q) => $q->where('status', 'Active'))
            ->with('site')
            ->first();

        if (!$activeSite || !$activeSite->site) {
            return [
                'success' => false,
                'message' => 'You are not assigned to any active site. Contact your administrator.',
                'status' => 403,
            ];
        }

        $site = $activeSite->site;

        if (!$site->latitude || !$site->longitude) {
            return [
                'success' => false,
                'message' => 'The assigned site does not have GPS coordinates configured. Contact your administrator.',
                'status' => 500,
            ];
        }

        return [
            'success' => true,
            'site' => $site,
        ];
    }

    protected function calculateHours(array &$data): void
    {
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $diffInHours = $checkIn->diffInMinutes($checkOut) / 60;
        $data['working_hours'] = round($diffInHours, 2);

        if ($data['working_hours'] > 8) {
            $data['overtime_hours'] = $data['working_hours'] - 8;
            $data['overtime'] = true;
        } else {
            $data['overtime_hours'] = 0;
            $data['overtime'] = false;
        }
    }
}
