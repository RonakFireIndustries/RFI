<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\NotificationService;

/**
 * Payments have no status - the record existing is the event. Useful mainly
 * so the finance team sees money land without opening the invoice.
 */
class PaymentObserver extends NotificationObserver
{
    public function created(Payment $payment): void
    {
        NotificationService::notify('payment.received', [
            'id' => $payment->invoice_id,
            'amount' => $this->money($payment->amount),
            'invoice' => $this->relationAttribute($payment, 'invoice', 'invoice_number', 'an invoice'),
        ], $this->currentActor());
    }
}
