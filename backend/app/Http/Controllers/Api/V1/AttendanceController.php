<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\GeoLocationService;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\AttendanceLocationLogResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use ApiResponse;

    protected AttendanceService $attendanceService;
    protected GeoLocationService $geoLocationService;

    public function __construct(AttendanceService $attendanceService, GeoLocationService $geoLocationService)
    {
        $this->attendanceService = $attendanceService;
        $this->geoLocationService = $geoLocationService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'employee_id', 'date', 'status', 'site_id', 'shift_id',
            'department_id', 'start_date', 'end_date', 'location_verified'
        ]);
        $perPage = (int) $request->input('per_page', 15);

        $attendances = $this->attendanceService->getAttendances($filters, $perPage);

        return $this->success('Attendances retrieved successfully', [
            'attendances' => $attendances
        ]);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->createAttendance($request->validated());

        return $this->success('Attendance created successfully', [
            'attendance' => $attendance
        ], 201);
    }

    public function show(Attendance $attendance): JsonResponse
    {
        $attendance->load(['employee', 'site', 'shift']);

        return $this->success('Attendance retrieved successfully', [
            'attendance' => new AttendanceResource($attendance)
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $attendance = $this->attendanceService->updateAttendance($attendance, $request->validated());

        return $this->success('Attendance updated successfully', [
            'attendance' => $attendance
        ]);
    }

    public function destroy(Attendance $attendance): JsonResponse
    {
        $this->attendanceService->deleteAttendance($attendance);

        return $this->success('Attendance deleted successfully');
    }

    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->error('Only employees can check in', [], 403);
        }

        $prerequisites = $this->attendanceService->validateCheckInPrerequisites($employee);

        if (!$prerequisites['success']) {
            return $this->error($prerequisites['message'], [], $prerequisites['status']);
        }

        $site = $prerequisites['site'];

        $result = $this->attendanceService->processCheckIn(
            $request->validated(),
            $employee,
            $site
        );

        if (!$result['success']) {
            return $this->error($result['message'], $result['data'] ?? [], $result['status']);
        }

        return $this->success($result['message'], $result['data'], $result['status']);
    }

    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->error('Only employees can check out', [], 403);
        }

        $prerequisites = $this->attendanceService->validateCheckInPrerequisites($employee);

        if (!$prerequisites['success']) {
            return $this->error($prerequisites['message'], [], $prerequisites['status']);
        }

        $site = $prerequisites['site'];

        $result = $this->attendanceService->processCheckOut(
            $request->validated(),
            $employee,
            $site
        );

        if (!$result['success']) {
            return $this->error($result['message'], $result['data'] ?? [], $result['status']);
        }

        return $this->success($result['message'], $result['data'], $result['status']);
    }

    public function myAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->error('Employee profile not found', [], 404);
        }

        $filters = array_merge(
            $request->only(['date', 'start_date', 'end_date', 'status']),
            ['employee_id' => $employee->id]
        );
        $perPage = (int) $request->input('per_page', 15);

        $attendances = $this->attendanceService->getAttendances($filters, $perPage);

        return $this->success('My attendance retrieved successfully', [
            'attendances' => AttendanceResource::collection($attendances)
        ]);
    }

    public function locationAudit(Request $request): JsonResponse
    {
        $filters = $request->only([
            'employee_id', 'site_id', 'date', 'start_date', 'end_date',
            'location_verified', 'status'
        ]);
        $perPage = (int) $request->input('per_page', 15);

        $query = Attendance::with(['employee.user', 'site'])
            ->whereNotNull('checkin_latitude');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['location_verified'])) {
            $query->where('location_verified', filter_var($filters['location_verified'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $logs = $query->orderBy('date', 'desc')->paginate($perPage);

        return $this->success('Attendance location logs retrieved successfully', [
            'logs' => AttendanceLocationLogResource::collection($logs)
        ]);
    }
}
