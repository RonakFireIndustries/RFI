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
        $this->authorize('attendance.view');

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
        $this->authorize('attendance.create');

        $attendance = $this->attendanceService->createAttendance($request->validated());

        return $this->success('Attendance created successfully', [
            'attendance' => $attendance
        ], 201);
    }

    public function show(Attendance $attendance): JsonResponse
    {
        $this->authorize('attendance.view');

        $attendance->load(['employee', 'site', 'checkoutSite', 'shift']);

        return $this->success('Attendance retrieved successfully', [
            'attendance' => new AttendanceResource($attendance)
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('attendance.update');

        $attendance = $this->attendanceService->updateAttendance($attendance, $request->validated());

        return $this->success('Attendance updated successfully', [
            'attendance' => $attendance
        ]);
    }

    public function destroy(Attendance $attendance): JsonResponse
    {
        $this->authorize('attendance.delete');

        $this->attendanceService->deleteAttendance($attendance);

        return $this->success('Attendance deleted successfully');
    }

    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;

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
        $employee = $request->user()->employee;

        if (!$employee) {
            return $this->error('Only employees can check out', [], 403);
        }

        $prerequisites = $this->attendanceService->validateCheckOutPrerequisites($employee);

        if (!$prerequisites['success']) {
            return $this->error($prerequisites['message'], [], $prerequisites['status']);
        }

        $result = $this->attendanceService->processCheckOut(
            $request->validated(),
            $employee
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

    public function myMonthlyReport(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return $this->error('Employee profile not found', [], 404);
        }

        if ($request->filled('month')) {
            $month = $request->input('month');
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
                return $this->error('Invalid month format. Use YYYY-MM.', [], 422);
            }
        } else {
            $month = Carbon::now()->format('Y-m');
        }

        $report = $this->attendanceService->getMonthlyReport($employee, $month);

        return $this->success('Monthly attendance report retrieved successfully', [
            'report' => $report,
        ]);
    }

}
