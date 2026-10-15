<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportHistory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class DailyReportService
{
    public function getReports(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DailyReport::with(['employee.designation', 'site', 'approver']);

        if (!empty($filters['employee_id'])) {
            if (is_array($filters['employee_id'])) {
                $query->whereIn('employee_id', $filters['employee_id']);
            } else {
                $query->where('employee_id', $filters['employee_id']);
            }
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
        // Filing a report is independent of attendance: a worker who forgot to
        // check in must still be able to document the day's work.
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
        $previousStatus = $report->status;

        // Moving a report to another day must not collide with a report that
        // already exists for that employee + date (rejected ones don't count).
        if (!empty($data['date'])) {
            $newDate = $data['date'] instanceof \DateTimeInterface
                ? $data['date']->format('Y-m-d')
                : (string) $data['date'];

            if ($newDate !== $report->date?->format('Y-m-d')) {
                $this->validateDuplicate($report->employee_id, $newDate, $report->id);
            }
        }

        if (isset($data['status']) && $data['status'] === 'Submitted' && $previousStatus !== 'Submitted') {
            $data['submitted_at'] = now();

            // A resubmission after rework clears any earlier decision so the
            // report is cleanly back in the manager's queue.
            if ($previousStatus === 'Rework Required') {
                $data['approved_at'] = null;
                $data['approved_by'] = null;
            }

            DailyReportHistory::create([
                'daily_report_id' => $report->id,
                'action_by' => auth()->id(),
                'action' => $previousStatus === 'Rework Required' ? 'Resubmitted' : 'Submitted',
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

    protected function validateDuplicate($employeeId, $date, ?int $ignoreId = null)
    {
        $query = DailyReport::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('status', '!=', 'Rejected');

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw new Exception("A Daily Report already exists for this employee on this date.");
        }
    }
}
