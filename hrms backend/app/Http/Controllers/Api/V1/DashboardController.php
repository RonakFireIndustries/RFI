<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // For now, since DB might not be fully seeded, we return safe data
        // matching the UI requirements.
        $totalEmployees = Employee::count();
        if ($totalEmployees === 0) {
            $totalEmployees = 1240; // Default to old mock data if db is empty
        }

        $presentEmployees = 985;
        $absentEmployees = 42;
        $onLeaveEmployees = 15;
        $activeSites = 24;

        $attendanceData = [
            ['name' => 'Oct 18', 'present' => 800],
            ['name' => 'Oct 19', 'present' => 850],
            ['name' => 'Oct 20', 'present' => 900],
            ['name' => 'Oct 21', 'present' => 820],
            ['name' => 'Oct 22', 'present' => 880],
            ['name' => 'Today', 'present' => $presentEmployees],
        ];

        $departmentData = [
            ['name' => 'Workers', 'value' => 640, 'color' => '#0B1B36'],
            ['name' => 'Site Mgmt', 'value' => 210, 'color' => '#4A5568'],
            ['name' => 'Engineering', 'value' => 185, 'color' => '#ED8936'],
            ['name' => 'HR & Accounts', 'value' => 205, 'color' => '#CBD5E0'],
        ];

        $upcomingBirthdays = [
            [
                'name' => 'Liza Anderson',
                'role' => 'Site Engineer',
                'date' => 'Tomorrow',
                'img' => '/static/images/avatar/4.jpg'
            ],
            [
                'name' => 'Robert King',
                'role' => 'Safety Officer',
                'date' => 'Oct 27',
                'img' => '/static/images/avatar/5.jpg'
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_employees' => $totalEmployees,
                'present_employees' => $presentEmployees,
                'absent_employees' => $absentEmployees,
                'on_leave_employees' => $onLeaveEmployees,
                'active_sites' => $activeSites,
                'attendance_data' => $attendanceData,
                'department_data' => $departmentData,
                'upcoming_birthdays' => $upcomingBirthdays,
            ],
        ]);
    }
}