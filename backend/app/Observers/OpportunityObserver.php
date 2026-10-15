<?php

namespace App\Observers;

use App\Models\Opportunity;
use App\Services\NotificationService;

/**
 * Opportunities are owned by the salesperson who created them (`user_id`).
 *
 * A plain stage move tells the owner; landing on Won or Lost is a business
 * outcome, so it tells the whole sales team instead. The two are mutually
 * exclusive so nobody is told twice about the same move.
 */
class OpportunityObserver extends NotificationObserver
{
    public const OUTCOMES = ['Won', 'Lost'];

    public function created(Opportunity $opportunity): void
    {
        NotificationService::notify('opportunity.created', $this->payload($opportunity));
    }

    public function updated(Opportunity $opportunity): void
    {
        if (! $this->changed($opportunity, 'stage')) {
            return;
        }

        // The actor is whoever is signed in, which is not necessarily the
        // owner - so an owner moving their own opportunity stays quiet while
        // a colleague's move still reaches them.
        $actor = auth()->id() !== null ? (int) auth()->id() : null;
        $owner = $this->actorId($opportunity, ['user_id']);
        $to = $opportunity->stage;

        if (in_array($to, self::OUTCOMES, true)) {
            $type = $to === 'Won' ? 'opportunity.won' : 'opportunity.lost';

            NotificationService::notify(
                $type,
                $this->payload($opportunity, ['reason' => $opportunity->lost_reason]),
                $actor,
            );

            return;
        }

        NotificationService::notify('opportunity.stage_changed', $this->payload($opportunity, [
            'from' => $this->previous($opportunity, 'stage'),
            'to' => $to,
        ]), $actor, [$owner]);
    }

    private function payload(Opportunity $opportunity, array $extra = []): array
    {
        return array_merge([
            'id' => $opportunity->id,
            'name' => $opportunity->name,
            'employee' => $this->relationAttribute($opportunity, 'user', 'name', 'A salesperson'),
            'building' => $this->relationAttribute($opportunity, 'building', 'name', 'an unassigned building'),
            'value' => $this->money($opportunity->actual_final_value ?? $opportunity->estimated_value),
            'reason' => $opportunity->lost_reason,
        ], $extra);
    }
}
