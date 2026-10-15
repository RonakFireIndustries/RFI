<?php

namespace App\Observers;

use App\Models\InventoryTransfer;
use App\Services\NotificationService;

/**
 * Inventory transfers are owned by `requested_by`. The enum is
 * pending, approved, rejected, completed.
 */
class InventoryTransferObserver extends NotificationObserver
{
    public function created(InventoryTransfer $transfer): void
    {
        NotificationService::notify('stock_transfer.created', $this->payload($transfer), $this->currentActor());
    }

    public function updated(InventoryTransfer $transfer): void
    {
        if (! $this->changed($transfer, 'status')) {
            return;
        }

        $actor = $this->currentActor();
        $requester = $this->actorId($transfer, ['requested_by']);

        match ($transfer->status) {
            'approved' => NotificationService::notify('stock_transfer.approved', $this->payload($transfer), $actor, [$requester]),
            default => null,
        };
    }

    private function payload(InventoryTransfer $transfer): array
    {
        return [
            'id' => $transfer->id,
            'employee' => $this->relationAttribute($transfer, 'requester', 'name', 'A user'),
            'product' => $this->relationAttribute($transfer, 'product', 'name', 'items'),
        ];
    }
}
