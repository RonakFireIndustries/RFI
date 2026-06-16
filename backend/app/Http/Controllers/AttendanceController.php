<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee.user');
        
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        return $query->get();
    }

    public function checkIn(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Only employees can check in'], 403);
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['check_in' => $now, 'status' => 'present']
        );

        if (!$attendance->wasRecentlyCreated && $attendance->check_in) {
            return response()->json(['message' => 'Already checked in today'], 400);
        }

        $attendance->update(['check_in' => $now]);

        return response()->json(['message' => 'Checked in successfully', 'attendance' => $attendance]);
    }

    public function checkOut(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Only employees can check out'], 403);
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json(['message' => 'Must check in first'], 400);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'Already checked out today'], 400);
        }

        $attendance->update(['check_out' => Carbon::now()]);

        return response()->json(['message' => 'Checked out successfully', 'attendance' => $attendance]);
    }
}
