<?php

namespace App\Observers;

use App\Models\Leave;
use App\Services\NotificationService;

/**
 * Leave is owned by an employee row, so "the requester" is the user behind
 * that employee.
 *
 * Statuses are driven by what LeaveRequestService actually writes, not by the
 * validation rules: Draft, Submitted, Approved, Rejected, Cancelled.
 */
class LeaveObserver extends NotificationObserver
{
    public function created(Leave $leave): void
    {
        // A Draft is the employee's own scratch pad - only a Submitted request
        // needs an approver's attention.
        if ($leave->status === 'Submitted') {
            $this->notifyApprovers($leave);
        }
    }

    public function updated(Leave $leave): void
    {
        if (! $this->changed($leave, 'status')) {
            return;
        }

        $actor = $this->currentActor() ?? $this->actorId($leave, ['approved_by']);

        switch ($leave->status) {
            case 'Submitted':
                $this->notifyApprovers($leave, $actor);
                break;

            case 'Approved':
                $this->notifyRequester($leave, 'leave.approved', $actor);
                break;

            case 'Rejected':
                $this->notifyRequester($leave, 'leave.rejected', $actor);
                break;

            case 'Cancelled':
                // The approver needs to know a request in their queue is gone.
                NotificationService::notify('leave.cancelled', $this->payload($leave), $actor);
                break;
        }
    }

    private function notifyApprovers(Leave $leave, ?int $actor = null): void
    {
        NotificationService::notify('leave.requested', $this->payload($leave), $actor);
    }

    private function notifyRequester(Leave $leave, string $type, ?int $actor = null): void
    {
        NotificationService::notify(
            $type,
            $this->payload($leave, ['reason' => $leave->comments]),
            $actor,
            [$this->userForEmployee($leave)],
        );
    }

    private function payload(Leave $leave, array $extra = []): array
    {
        return array_merge([
            'id' => $leave->id,
            'employee' => $this->requesterName($leave),
            'days' => $leave->total_days,
            'leave_type' => $this->relationAttribute($leave, 'leaveType', 'name', 'leave'),
            'from' => $this->formatDate($leave->start_date),
            'to' => $this->formatDate($leave->end_date),
            'reason' => $leave->comments,
        ], $extra);
    }

    private function requesterName(Leave $leave): string
    {
        $employee = $leave->getAttribute('employee');

        return $employee?->full_name
            ?? $employee?->user?->name
            ?? 'An employee';
    }
}
