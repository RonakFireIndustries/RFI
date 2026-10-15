<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Services\NotificationService;

/**
 * Purchase orders are owned by `requested_by`. The written statuses are
 * Pending, Approved, Rejected, Received and Partially Received.
 */
class PurchaseOrderObserver extends NotificationObserver
{
    public function created(PurchaseOrder $order): void
    {
        NotificationService::notify('purchase_order.created', $this->payload($order), $this->currentActor());
    }

    public function updated(PurchaseOrder $order): void
    {
        if (! $this->changed($order, 'status')) {
            return;
        }

        $actor = $this->currentActor();
        $requester = $this->actorId($order, ['requested_by']);

        match ($order->status) {
            'Approved' => NotificationService::notify('purchase_order.approved', $this->payload($order), $actor, [$requester]),
            'Rejected' => NotificationService::notify(
                'purchase_order.rejected',
                $this->payload($order, ['reason' => $order->remarks ?? $order->rejection_reason]),
                $actor,
                [$requester]
            ),
            'Received', 'Partially Received' => NotificationService::notify('goods_receipt.created', $this->payload($order), $actor),
            default => null,
        };
    }

    private function payload(PurchaseOrder $order, array $extra = []): array
    {
        return array_merge([
            'id' => $order->id,
            'po_number' => $order->po_number,
            'supplier' => $this->relationAttribute($order, 'supplier', 'name'),
            'employee' => $this->relationAttribute($order, 'requester', 'name', 'A user'),
            'amount' => $this->money($order->total_amount),
        ], $extra);
    }
}
