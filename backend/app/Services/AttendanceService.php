<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Site;
use App\Models\Shift;
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

        // A human-supplied check-out supersedes an auto-generated one.
        if (array_key_exists('check_out', $data) && $data['check_out'] !== null && $attendance->is_auto_checkout) {
            $data['is_auto_checkout'] = false;
            if (!array_key_exists('remarks', $data)) {
                $data['remarks'] = $this->withoutAutoCheckoutRemark($attendance->remarks);
            }
        }

        $attendance->update($data);
        return $attendance->fresh(['employee', 'site', 'shift']);
    }

    /**
     * Strip the auto check-out note from a remarks string, returning null when
     * nothing else remains.
     */
    protected function withoutAutoCheckoutRemark(?string $remarks): ?string
    {
        $cleaned = trim(str_replace(Attendance::AUTO_CHECKOUT_REMARK, '', (string) $remarks), " |");

        return $cleaned === '' ? null : $cleaned;
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
            $maxAccuracy = max($allowedRadius, 100);
            if ($accuracy === null || $accuracy > $maxAccuracy) {
                return [
                    'success' => false,
                    'message' => 'GPS accuracy is too low (' . round($accuracy ?? 0) . 'm). Move to an open area with better GPS signal.',
                    'data' => [
                        'accuracy' => $accuracy,
                        'max_accuracy' => $maxAccuracy,
                    ],
                    'status' => 403,
                ];
            }
        } else {
            $accuracy = $data['accuracy'] ?? null;
        }

        $locationVerified = $this->geoLocation->isWithinRadius($distance, $allowedRadius);

        // Detect late check-in based on shift
        $isLate = false;
        $lateMinutes = null;
        $status = 'Present';

        $shiftId = $data['shift_id'] ?? null;
        if (!$shiftId && $employee) {
            $employeeShift = $employee->employeeShifts()->first();
            if ($employeeShift) {
                $shiftId = $employeeShift->shift_id;
            }
        }
        $shift = $shiftId ? Shift::find($shiftId) : Shift::where('status', 'Active')->first();

        if ($shift && $shift->start_time) {
            $shiftStart = Carbon::parse($today . ' ' . $shift->start_time);
            $gracePeriod = $shift->grace_period ?? 0;
            $lateThreshold = $shift->late_threshold ?? 0;

            $graceEnd = $shiftStart->copy()->addMinutes($gracePeriod);
            $lateEnd = $shiftStart->copy()->addMinutes($lateThreshold);

            if ($now->greaterThan($lateEnd) && $lateThreshold > 0) {
                $isLate = true;
                $lateMinutes = $now->diffInMinutes($shiftStart);
                $status = 'Late';
            }
        }

        $attendanceData = [
            'employee_id' => $employee->id,
            'site_id' => $site->id,
            'date' => $today,
            'check_in' => $now,
            'status' => $status,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
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
        } elseif ($shift) {
            $attendanceData['shift_id'] = $shift->id;
        } elseif ($shiftId) {
            $attendanceData['shift_id'] = $shiftId;
        }

        $attendance = Attendance::create($attendanceData);

        return [
            'success' => true,
            'message' => 'Checked in successfully' . ($isLate ? ' (Late by ' . $lateMinutes . ' minutes)' : ''),
            'data' => [
                'attendance' => $attendance,
                'distance' => round($distance, 2),
                'allowed_radius' => $allowedRadius,
                'location_verified' => $locationVerified,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
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

        if ($attendance->check_out && !$attendance->is_auto_checkout) {
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
            $maxAccuracy = max($allowedRadius, 100);
            if ($accuracy === null || $accuracy > $maxAccuracy) {
                return [
                    'success' => false,
                    'message' => 'GPS accuracy is too low (' . round($accuracy ?? 0) . 'm). Move to an open area with better GPS signal.',
                    'data' => [
                        'accuracy' => $accuracy,
                        'max_accuracy' => $maxAccuracy,
                    ],
                    'status' => 403,
                ];
            }
        } else {
            $accuracy = $data['accuracy'] ?? null;
        }

        $locationVerified = $this->geoLocation->isWithinRadius($distance, $allowedRadius);

        $remarks = $attendance->is_auto_checkout
            ? $this->withoutAutoCheckoutRemark($attendance->remarks)
            : $attendance->remarks;

        $updateData = [
            'check_out' => $now,
            'is_auto_checkout' => false,
            'remarks' => $remarks,
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

        $activeSite = $employee->employeeSites()->with('site')->first();

        if (!$activeSite || !$activeSite->site) {
            return [
                'success' => false,
                'message' => 'You are not assigned to any site. Contact your administrator.',
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

    public function getMonthlyReport(Employee $employee, string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $today = Carbon::today();

        $records = Attendance::with('site')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($attendance) => Carbon::parse($attendance->date)->toDateString());

        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        $summary = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'leave' => 0,
            'half_day' => 0,
            'holidays' => 0,
            'weekly_off' => 0,
            'working_days' => 0,
            'marked_days' => 0,
            'total_working_hours' => 0,
            'total_overtime_hours' => 0,
        ];

        $days = [];
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $key = $cursor->toDateString();
            $record = $records->get($key);

            $isWeeklyOff = $cursor->dayOfWeek === Carbon::SUNDAY;
            $isHoliday = in_array($key, $holidays, true);
            $isFuture = $cursor->gt($today);

            if (!$isWeeklyOff && !$isHoliday && !$isFuture) {
                $summary['working_days']++;
            }

            if ($record) {
                $raw = strtolower((string) ($record->status ?? 'present'));

                if (str_contains($raw, 'leave')) {
                    $summary['leave']++;
                } elseif (str_contains($raw, 'half')) {
                    $summary['half_day']++;
                } elseif ($raw === 'absent') {
                    $summary['absent']++;
                } elseif ($raw === 'late') {
                    $summary['late']++;
                } else {
                    $summary['present']++;
                }

                $summary['marked_days']++;
                $summary['total_working_hours'] += (float) $record->working_hours;
                $summary['total_overtime_hours'] += (float) $record->overtime_hours;

                $days[] = [
                    'date' => $key,
                    'status' => $record->status ?: 'Present',
                    'check_in' => $record->check_in?->toDateTimeString(),
                    'check_out' => $record->check_out?->toDateTimeString(),
                    'is_auto_checkout' => (bool) $record->is_auto_checkout,
                    'working_hours' => $record->working_hours !== null ? (float) $record->working_hours : null,
                    'overtime_hours' => $record->overtime_hours !== null ? (float) $record->overtime_hours : null,
                    'site' => $record->site?->name,
                    'location_verified' => (bool) $record->location_verified,
                ];
            } elseif ($isHoliday) {
                $summary['holidays']++;
                $days[] = $this->emptyReportDay($key, 'Holiday');
            } elseif ($isWeeklyOff) {
                $summary['weekly_off']++;
                $days[] = $this->emptyReportDay($key, 'Weekly Off');
            } elseif ($isFuture) {
                $days[] = $this->emptyReportDay($key, 'Upcoming');
            } else {
                $summary['absent']++;
                $days[] = $this->emptyReportDay($key, 'Absent');
            }

            $cursor->addDay();
        }

        $summary['total_working_hours'] = round($summary['total_working_hours'], 2);
        $summary['total_overtime_hours'] = round($summary['total_overtime_hours'], 2);

        $attended = $summary['present'] + $summary['late'] + ($summary['half_day'] * 0.5);
        $summary['attendance_percentage'] = $summary['working_days'] > 0
            ? min(100, (int) round(($attended / $summary['working_days']) * 100))
            : 0;

        return [
            'month' => $start->format('Y-m'),
            'month_label' => $start->format('F Y'),
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'emp_id' => $employee->emp_id,
            ],
            'days' => $days,
            'summary' => $summary,
        ];
    }

    protected function emptyReportDay(string $date, string $status): array
    {
        return [
            'date' => $date,
            'status' => $status,
            'check_in' => null,
            'check_out' => null,
            'is_auto_checkout' => false,
            'working_hours' => null,
            'overtime_hours' => null,
            'site' => null,
            'location_verified' => false,
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
