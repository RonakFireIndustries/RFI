<?php

namespace App\Observers;

use App\Models\SiteVisit;
use App\Services\NotificationService;

/**
 * Site visits have no status column - creation is the event, and the people
 * who need to know are the rest of the survey team, not the person who just
 * booked it.
 */
class SiteVisitObserver extends NotificationObserver
{
    public function created(SiteVisit $visit): void
    {
        NotificationService::notify('site_visit.scheduled', [
            'id' => $visit->id,
            'employee' => $this->relationAttribute($visit, 'user', 'name', 'A user'),
            'building' => $this->relationAttribute($visit, 'building', 'name', 'a building'),
            'date' => $this->formatDate($visit->visit_date),
        ], $this->currentActor());
    }
}
