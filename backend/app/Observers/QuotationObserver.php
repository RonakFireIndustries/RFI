<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Services\NotificationService;

/**
 * Quotations are the one model in the codebase with proper status constants:
 * draft, sent, accepted, rejected.
 */
class QuotationObserver extends NotificationObserver
{
    public function created(Quotation $quotation): void
    {
        NotificationService::notify('quotation.created', $this->payload($quotation), $this->currentActor());
    }

    public function updated(Quotation $quotation): void
    {
        if (! $this->changed($quotation, 'status')) {
            return;
        }

        $type = match ($quotation->status) {
            Quotation::STATUS_ACCEPTED => 'quotation.accepted',
            Quotation::STATUS_REJECTED => 'quotation.rejected',
            default => null,
        };

        if ($type === null) {
            return;
        }

        NotificationService::notify($type, $this->payload($quotation), $this->currentActor());
    }

    private function payload(Quotation $quotation): array
    {
        return [
            'id' => $quotation->id,
            'quotation_no' => $quotation->quotation_no,
            'building' => $quotation->display_building_name
                ?? $this->relationAttribute($quotation, 'building', 'name', 'a building'),
            'employee' => $this->relationAttribute($quotation, 'creator', 'name', 'A user'),
        ];
    }
}
