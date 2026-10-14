<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\LeaveHistory;
use App\Models\Attendance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class LeaveRequestService
{
    protected LeaveBalanceService $balanceService;

    public function __construct(LeaveBalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function getLeaves(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Leave::with(['employee.user', 'employee.department', 'leaveType', 'approver']);

        if (!empty($filters['employee_id'])) {
            if (is_array($filters['employee_id'])) {
                $query->whereIn('employee_id', $filters['employee_id']);
            } else {
                $query->where('employee_id', $filters['employee_id']);
            }
        }
        if (!empty($filters['status'])) {
            // The dashboard approval widget asks for the still-undecided rows in
            // one request (`status=Submitted,Pending`), so accept a list as well
            // as the single-status value the Leave Management pages send.
            $statuses = is_array($filters['status'])
                ? $filters['status']
                : explode(',', (string) $filters['status']);

            $statuses = array_values(array_filter(
                array_map('trim', $statuses),
                fn ($status) => $status !== ''
            ));

            if (count($statuses) > 1) {
                $query->whereIn('status', $statuses);
            } elseif ($statuses) {
                $query->where('status', $statuses[0]);
            }
        }
        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createLeave(array $data): Leave
    {
        $data['total_days'] = $this->calculateDays($data['start_date'], $data['end_date'], $data['is_half_day'] ?? false);
        
        if (empty($data['status'])) {
            $data['status'] = 'Draft';
        }

        if ($data['status'] === 'Submitted') {
            $data['applied_at'] = now();
        }

        $leave = Leave::create($data);

        LeaveHistory::create([
            'leave_id' => $leave->id,
            'action_by' => auth()->id(),
            'action' => $data['status'],
        ]);

        return $leave;
    }

    public function updateLeave(Leave $leave, array $data): Leave
    {
        if (isset($data['start_date']) || isset($data['end_date']) || isset($data['is_half_day'])) {
            $startDate = $data['start_date'] ?? $leave->start_date;
            $endDate = $data['end_date'] ?? $leave->end_date;
            $isHalfDay = $data['is_half_day'] ?? $leave->is_half_day;
            $data['total_days'] = $this->calculateDays($startDate, $endDate, $isHalfDay);
        }

        if (isset($data['status']) && $data['status'] === 'Submitted' && $leave->status !== 'Submitted') {
            $data['applied_at'] = now();
            LeaveHistory::create([
                'leave_id' => $leave->id,
                'action_by' => auth()->id(),
                'action' => 'Submitted',
            ]);
        }

        $leave->update($data);
        return $leave;
    }

    public function approve(Leave $leave, ?string $comments = null): Leave
    {
        if ($leave->status !== 'Submitted') {
            throw new Exception("Only submitted leaves can be approved.");
        }

        // Deduct Balance
        $year = Carbon::parse($leave->start_date)->year;
        $this->balanceService->deductBalance($leave->employee_id, $leave->leave_type_id, $year, $leave->total_days);

        $leave->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'comments' => $comments,
        ]);

        LeaveHistory::create([
            'leave_id' => $leave->id,
            'action_by' => auth()->id(),
            'action' => 'Approved',
            'comments' => $comments,
        ]);

        $this->syncAttendance($leave);

        return $leave;
    }

    public function reject(Leave $leave, string $comments): Leave
    {
        $leave->update([
            'status' => 'Rejected',
            'comments' => $comments,
        ]);

        LeaveHistory::create([
            'leave_id' => $leave->id,
            'action_by' => auth()->id(),
            'action' => 'Rejected',
            'comments' => $comments,
        ]);

        return $leave;
    }

    public function cancel(Leave $leave, string $comments): Leave
    {
        if ($leave->status === 'Approved') {
            // Refund Balance
            $year = Carbon::parse($leave->start_date)->year;
            $this->balanceService->refundBalance($leave->employee_id, $leave->leave_type_id, $year, $leave->total_days);
            
            // Remove Attendance
            $this->removeAttendance($leave);
        }

        $leave->update([
            'status' => 'Cancelled',
            'comments' => $comments,
        ]);

        LeaveHistory::create([
            'leave_id' => $leave->id,
            'action_by' => auth()->id(),
            'action' => 'Cancelled',
            'comments' => $comments,
        ]);

        return $leave;
    }

    public function calculateDays($startDate, $endDate, $isHalfDay)
    {
        if ($isHalfDay) {
            return 0.5;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $days = 0;
        $holidays = Holiday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->pluck('date')->toArray();

        while ($start <= $end) {
            // Skip Sundays (0) and holidays
            if ($start->dayOfWeek !== Carbon::SUNDAY && !in_array($start->format('Y-m-d'), $holidays)) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }

    protected function syncAttendance(Leave $leave)
    {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        
        $holidays = Holiday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->pluck('date')->toArray();

        while ($start <= $end) {
            if ($start->dayOfWeek !== Carbon::SUNDAY && !in_array($start->format('Y-m-d'), $holidays)) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $leave->employee_id,
                        'date' => $start->format('Y-m-d'),
                    ],
                    [
                        'status' => 'On Leave',
                        'notes' => 'Leave Approved: ' . ($leave->leaveType->name ?? 'Leave'),
                        'site_id' => null, // clear site
                    ]
                );
            }
            $start->addDay();
        }
    }

    protected function removeAttendance(Leave $leave)
    {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);

        Attendance::where('employee_id', $leave->employee_id)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where('status', 'On Leave')
            ->delete();
    }
}
