<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function generate(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'creator']);
        $company = CompanySetting::first();

        $data = [
            'invoice' => $invoice,
            'customer' => $invoice->customer,
            'items' => $invoice->items,
            'company' => $company,
            'bank_details' => $company && $company->bank_name ? [
                'bank_name' => $company->bank_name,
                'account_name' => $company->bank_account_name,
                'account_number' => $company->bank_account_number,
                'ifsc_code' => $company->bank_ifsc_code,
                'swift_code' => $company->bank_swift_code,
                'branch' => $company->bank_branch,
            ] : null,
            'signature' => $company && ($company->signatory_name || $company->signature_image) ? [
                'signatory_name' => $company->signatory_name,
                'signature_url' => $company->signature_image ? public_path('storage/' . $company->signature_image) : null,
            ] : null,
            'generated_at' => now()->format('d M Y, h:i A'),
            'generated_by' => $invoice->creator?->name ?? 'System',
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}
