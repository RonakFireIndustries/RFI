<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function generate(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'creator']);
        
        $data = [
            'invoice' => $invoice,
            'customer' => $invoice->customer,
            'items' => $invoice->items
        ];

        // Ensure we have a view for this. We will create resources/views/pdf/invoice.blade.php
        $pdf = Pdf::loadView('pdf.invoice', $data);
        
        return $pdf;
    }
}
