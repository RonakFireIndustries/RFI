<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportHistory;
use App\Models\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class DailyReportService
{
    public function getReports(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DailyReport::with(['employee.designation', 'site', 'approver']);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        } elseif (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    public function createReport(array $data): DailyReport
    {
        $this->validateAttendance($data['employee_id'], $data['date']);
        $this->validateDuplicate($data['employee_id'], $data['date']);

        if (empty($data['status'])) {
            $data['status'] = 'Draft';
        }

        if ($data['status'] === 'Submitted') {
            $data['submitted_at'] = now();
        }

        return DailyReport::create($data);
    }

    public function updateReport(DailyReport $report, array $data): DailyReport
    {
        if (isset($data['status']) && $data['status'] === 'Submitted' && $report->status !== 'Submitted') {
            $data['submitted_at'] = now();
            // Log submission
            DailyReportHistory::create([
                'daily_report_id' => $report->id,
                'action_by' => auth()->id(),
                'action' => 'Submitted',
            ]);
        }

        $report->update($data);
        return $report->fresh(['employee', 'site', 'approver']);
    }

    public function deleteReport(DailyReport $report): void
    {
        $report->delete();
    }

    public function approve(DailyReport $report, ?string $comments = null): DailyReport
    {
        $report->update([
            'status' => 'Approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        DailyReportHistory::create([
            'daily_report_id' => $report->id,
            'action_by' => auth()->id(),
            'action' => 'Approved',
            'comments' => $comments,
        ]);

        return $report->fresh(['approver']);
    }

    public function reject(DailyReport $report, string $comments): DailyReport
    {
        $report->update([
            'status' => 'Rejected',
        ]);

        DailyReportHistory::create([
            'daily_report_id' => $report->id,
            'action_by' => auth()->id(),
            'action' => 'Rejected',
            'comments' => $comments,
        ]);

        return $report;
    }

    public function rework(DailyReport $report, string $comments): DailyReport
    {
        $report->update([
            'status' => 'Rework Required',
        ]);

        DailyReportHistory::create([
            'daily_report_id' => $report->id,
            'action_by' => auth()->id(),
            'action' => 'Rework Requested',
            'comments' => $comments,
        ]);

        return $report;
    }

    protected function validateAttendance($employeeId, $date)
    {
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('status', 'Present')
            ->first();

        if (!$attendance) {
            throw new Exception("Employee was not marked 'Present' on this date.");
        }
    }

    protected function validateDuplicate($employeeId, $date)
    {
        $exists = DailyReport::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('status', '!=', 'Rejected')
            ->exists();

        if ($exists) {
            throw new Exception("A Daily Report already exists for this employee on this date.");
        }
    }
}
