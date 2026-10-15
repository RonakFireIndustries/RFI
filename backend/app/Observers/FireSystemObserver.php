<?php

namespace App\Observers;

use App\Models\FireSystem;
use App\Services\NotificationService;

/**
 * Fire systems / extinguishers are compliance assets. The meaningful event is
 * a status move away from "Available" - something is out of service and the
 * compliance team needs to know.
 *
 * The status constants live on the FireSystem model even though the module is
 * called Fire Extinguishers.
 */
class FireSystemObserver extends NotificationObserver
{
    public function updated(FireSystem $system): void
    {
        if (! $this->changed($system, 'status')) {
            return;
        }

        $to = $system->status;

        // Coming back into service is routine; leaving it is not.
        if ($to === FireSystem::STATUS_AVAILABLE) {
            return;
        }

        NotificationService::notify('extinguisher.status_changed', [
            'id' => $system->id,
            'serial' => $system->label
                ?? $system->serial_number
                ?? $this->relationAttribute($system, 'building', 'name', 'an asset'),
            'status' => $to,
        ], $this->currentActor());
    }
}
