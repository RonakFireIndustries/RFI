<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Services\NotificationService;

/**
 * Sales orders are owned by `created_by`. Creation lands on "Pending", so the
 * approval queue needs telling straight away.
 */
class SalesOrderObserver extends NotificationObserver
{
    public function created(SalesOrder $order): void
    {
        NotificationService::notify('sales_order.created', $this->payload($order), $this->currentActor());
    }

    public function updated(SalesOrder $order): void
    {
        if (! $this->changed($order, 'status')) {
            return;
        }

        $type = match ($order->status) {
            'Approved' => 'sales_order.approved',
            'Rejected' => 'sales_order.rejected',
            default => null,
        };

        if ($type === null) {
            return;
        }

        NotificationService::notify(
            $type,
            $this->payload($order, ['reason' => $order->remarks ?? $order->rejection_reason]),
            $this->currentActor(),
        );
    }

    private function payload(SalesOrder $order, array $extra = []): array
    {
        return array_merge([
            'id' => $order->id,
            'so_number' => $order->so_number,
            'customer' => $this->relationAttribute($order, 'customer', 'name'),
            'employee' => $this->relationAttribute($order, 'creator', 'name', 'A user'),
            'amount' => $this->money($order->total_amount),
        ], $extra);
    }
}
