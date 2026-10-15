<?php

namespace App\Observers;

use App\Models\FollowUp;
use App\Services\NotificationService;

/**
 * Follow-ups are owned by the user who scheduled them (`user_id`).
 *
 * The due/overdue reminders themselves come from `notifications:scan`; this
 * observer only tells the sales team that a follow-up now exists.
 */
class FollowUpObserver extends NotificationObserver
{
    public function created(FollowUp $followUp): void
    {
        NotificationService::notify('followup.created', [
            'id' => $followUp->id,
            'employee' => $this->relationAttribute($followUp, 'user', 'name', 'A user'),
            'type' => $followUp->type,
            'date' => $this->formatDate($followUp->reminder_date),
        ]);
    }
}
