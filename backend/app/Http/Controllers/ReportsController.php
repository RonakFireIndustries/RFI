<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function salesReport(Request $request)
    {
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : now()->endOfMonth();

        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->with('customer')
            ->get();

        $data = [
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'total_invoices' => $invoices->count(),
            'total_revenue' => $invoices->sum('total_amount'),
            'invoices' => $invoices,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Sales report generated',
            'data' => $data
        ], 200);
    }

    public function paymentReport(Request $request)
    {
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : now()->endOfMonth();

        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->with('invoice')
            ->get();

        $data = [
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'payments' => $payments,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Payment report generated',
            'data' => $data
        ], 200);
    }

    public function attendanceReport(Request $request)
    {
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $attendance = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('employee.user')
            ->get();

        $data = [
            'month' => $month,
            'year' => $year,
            'total_records' => $attendance->count(),
            'present' => $attendance->where('status', 'Present')->count(),
            'absent' => $attendance->where('status', 'Absent')->count(),
            'late' => $attendance->where('status', 'Late')->count(),
            'half_day' => $attendance->where('status', 'Half Day')->count(),
            'attendance_records' => $attendance,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Attendance report generated',
            'data' => $data
        ], 200);
    }

    public function leaveReport(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $query = Leave::with('employee.user');

        if ($month && $year) {
            $query->whereMonth('from_date', $month)
                ->whereYear('from_date', $year);
        }

        $leaves = $query->get();

        $data = [
            'period' => $month && $year ? "$month/$year" : 'All',
            'total_leaves' => $leaves->count(),
            'approved' => $leaves->where('status', 'Approved')->count(),
            'rejected' => $leaves->where('status', 'Rejected')->count(),
            'pending' => $leaves->where('status', 'Pending')->count(),
            'leaves' => $leaves,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Leave report generated',
            'data' => $data
        ], 200);
    }

    public function employeeReport(Request $request)
    {
        $employees = Employee::with('user', 'department', 'designation')
            ->where('status', $request->query('status', 'Active'))
            ->get();

        $data = [
            'total_employees' => $employees->count(),
            'by_department' => $employees->groupBy('department_id')->map->count(),
            'by_designation' => $employees->groupBy('designation_id')->map->count(),
            'employees' => $employees,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Employee report generated',
            'data' => $data
        ], 200);
    }
}
