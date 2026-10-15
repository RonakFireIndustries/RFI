<?php

namespace App\Observers;

use App\Models\StockRequest;
use App\Services\NotificationService;

/**
 * Stock requests are owned by `requested_by`. The written statuses are the
 * lower-case enum from the migration: requested, approved, issued, received.
 */
class StockRequestObserver extends NotificationObserver
{
    public function created(StockRequest $request): void
    {
        // A row is born "requested", which is exactly when the store team
        // needs to see it.
        NotificationService::notify('stock_request.created', $this->payload($request), $this->currentActor());
    }

    public function updated(StockRequest $request): void
    {
        if (! $this->changed($request, 'status')) {
            return;
        }

        $actor = $this->currentActor();
        $requester = $this->actorId($request, ['requested_by']);

        match ($request->status) {
            'approved' => NotificationService::notify('stock_request.approved', $this->payload($request), $actor, [$requester]),
            'issued' => NotificationService::notify('stock_request.issued', $this->payload($request), $actor, [$requester]),
            default => null,
        };
    }

    private function payload(StockRequest $request): array
    {
        return [
            'id' => $request->id,
            'employee' => $this->relationAttribute($request, 'requester', 'name')
                ?? $this->relationAttribute($request, 'requestedBy', 'name', 'A user'),
            'product' => $this->relationAttribute($request, 'product', 'name', 'items'),
        ];
    }
}
