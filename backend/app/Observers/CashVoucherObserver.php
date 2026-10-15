<?php

namespace App\Observers;

use App\Models\CashVoucher;
use App\Services\NotificationService;

/**
 * Cash vouchers are owned by the user who raised them (`created_by`).
 *
 * There is no separate "submit" step in this app - a voucher is created as
 * Draft and then approved or rejected - so creation is the moment the
 * approvers first need to see it.
 */
class CashVoucherObserver extends NotificationObserver
{
    public function created(CashVoucher $voucher): void
    {
        NotificationService::notify('cash_voucher.submitted', $this->payload($voucher));
    }

    public function updated(CashVoucher $voucher): void
    {
        if (! $this->changed($voucher, 'status')) {
            return;
        }

        $actor = $this->currentActor() ?? $this->actorId($voucher, ['approved_by']);

        $type = match ($voucher->status) {
            'Approved' => 'cash_voucher.approved',
            'Rejected' => 'cash_voucher.rejected',
            default => null,
        };

        if ($type === null) {
            return;
        }

        $owner = $this->actorId($voucher, ['created_by']);

        NotificationService::notify($type, $this->payload($voucher), $actor, [$owner]);
    }

    private function payload(CashVoucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'employee' => $this->relationAttribute($voucher, 'employee', 'full_name'),
            'amount' => $this->money($voucher->amount),
            'purpose' => $voucher->purpose ?? $voucher->category ?? 'expense',
            'reason' => $voucher->remarks ?? $voucher->reason,
        ];
    }
}
