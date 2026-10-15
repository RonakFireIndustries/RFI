<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\NotificationService;

/**
 * Invoices are owned by `created_by`.
 *
 * Only creation is handled here. Overdue detection is deliberately NOT done
 * from a status transition, because invoice status is written inconsistently
 * across the codebase (lower case in one validation rule, capitalised in
 * another, plus "Unpaid"/"Partially Paid" values that appear in neither) -
 * the daily scan keys off `due_date` instead, which is unambiguous.
 */
class InvoiceObserver extends NotificationObserver
{
    public function created(Invoice $invoice): void
    {
        NotificationService::notify('invoice.created', [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer' => $this->relationAttribute($invoice, 'customer', 'name'),
            'amount' => $this->money($invoice->total_amount ?? $invoice->grand_total),
            'due_date' => $this->formatDate($invoice->due_date),
        ], $this->currentActor());
    }
}
