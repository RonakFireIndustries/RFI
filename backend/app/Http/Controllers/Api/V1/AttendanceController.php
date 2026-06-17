<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use ApiResponse;

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display a listing of the attendances.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);

        $filters = $request->only(['employee_id', 'date', 'status', 'site_id', 'shift_id', 'department_id', 'start_date', 'end_date']);
        $perPage = (int) $request->input('per_page', 15);

        $attendances = $this->attendanceService->getAttendances($filters, $perPage);

        return $this->success('Attendances retrieved successfully', [
            'attendances' => $attendances
        ]);
    }

    /**
     * Store a newly created attendance (manual entry).
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);

        $attendance = $this->attendanceService->createAttendance($request->validated());

        return $this->success('Attendance created successfully', [
            'attendance' => $attendance
        ], 201);
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance): JsonResponse
    {
        $this->authorize('view', $attendance);

        $attendance->load(['employee', 'site', 'shift']);

        return $this->success('Attendance retrieved successfully', [
            'attendance' => $attendance
        ]);
    }

    /**
     * Update the specified attendance.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);

        $attendance = $this->attendanceService->updateAttendance($attendance, $request->validated());

        return $this->success('Attendance updated successfully', [
            'attendance' => $attendance
        ]);
    }

    /**
     * Remove the specified attendance.
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $this->authorize('delete', $attendance);

        $this->attendanceService->deleteAttendance($attendance);

        return $this->success('Attendance deleted successfully');
    }

    /**
     * Check in an employee.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->error('Only employees can check in', 403);
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['check_in' => $now, 'status' => 'Present', 'site_id' => $request->site_id, 'shift_id' => $request->shift_id]
        );

        if (!$attendance->wasRecentlyCreated && $attendance->check_in) {
            return $this->error('Already checked in today', 400);
        }

        $attendance->update(['check_in' => $now]);

        return $this->success('Checked in successfully', [
            'attendance' => $attendance
        ]);
    }

    /**
     * Check out an employee.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return $this->error('Only employees can check out', 403);
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if (!$attendance || !$attendance->check_in) {
            return $this->error('Must check in first', 400);
        }

        if ($attendance->check_out) {
            return $this->error('Already checked out today', 400);
        }

        $this->attendanceService->updateAttendance($attendance, ['check_out' => Carbon::now()]);

        return $this->success('Checked out successfully', [
            'attendance' => $attendance->fresh()
        ]);
    }
}
