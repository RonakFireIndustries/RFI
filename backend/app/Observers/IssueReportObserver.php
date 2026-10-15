<?php

namespace App\Observers;

use App\Models\IssueReport;
use App\Services\NotificationService;

/**
 * Issue reports are owned by the user who filed them (`user_id`).
 *
 * Written statuses are open, in_progress, resolved, closed - lower case.
 */
class IssueReportObserver extends NotificationObserver
{
    public function created(IssueReport $report): void
    {
        NotificationService::notify('issue_report.created', [
            'id' => $report->id,
            'employee' => $this->relationAttribute($report, 'user', 'name', 'A user'),
            'subject' => $report->title,
        ]);
    }

    public function updated(IssueReport $report): void
    {
        if (! $this->changed($report, 'status')) {
            return;
        }

        if (! in_array($report->status, ['resolved', 'closed'], true)) {
            return;
        }

        $reporter = $this->actorId($report, ['user_id']);

        NotificationService::notify(
            'issue_report.resolved',
            [
                'id' => $report->id,
                'subject' => $report->title,
            ],
            $this->currentActor() ?? $this->actorId($report, ['resolved_by']),
            [$reporter],
        );
    }
}
