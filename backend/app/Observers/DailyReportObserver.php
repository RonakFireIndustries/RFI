<?php

namespace App\Observers;

use App\Models\DailyReport;
use App\Services\NotificationService;

/**
 * Daily reports are owned by an employee row.
 *
 * The written statuses are Draft, Submitted, Rework Required, Approved and
 * Rejected - only the last four need to tell anyone anything.
 */
class DailyReportObserver extends NotificationObserver
{
    public function created(DailyReport $report): void
    {
        if ($report->status === 'Submitted') {
            $this->notifyApprovers($report);
        }
    }

    public function updated(DailyReport $report): void
    {
        if (! $this->changed($report, 'status')) {
            return;
        }

        $actor = $this->currentActor() ?? $this->actorId($report, ['approved_by']);

        match ($report->status) {
            'Submitted' => $this->notifyApprovers($report, $actor),
            'Approved' => $this->notifySubmitter($report, 'daily_report.approved', $actor),
            'Rejected' => $this->notifySubmitter($report, 'daily_report.rejected', $actor),
            'Rework Required' => $this->notifySubmitter($report, 'daily_report.rework', $actor),
            default => null,
        };
    }

    private function notifyApprovers(DailyReport $report, ?int $actor = null): void
    {
        NotificationService::notify('daily_report.submitted', $this->payload($report), $actor);
    }

    private function notifySubmitter(DailyReport $report, string $type, ?int $actor = null): void
    {
        NotificationService::notify(
            $type,
            $this->payload($report),
            $actor,
            [$this->userForEmployee($report)],
        );
    }

    private function payload(DailyReport $report): array
    {
        $employee = $report->getAttribute('employee');

        return [
            'id' => $report->id,
            'employee' => $employee?->full_name ?? $employee?->user?->name ?? 'An employee',
            'date' => $this->formatDate($report->date ?? $report->report_date),
            'reason' => $report->admin_notes ?? $report->comments,
        ];
    }
}
