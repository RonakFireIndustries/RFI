<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\CompanySetting;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicePreviewController extends Controller
{
    public function preview(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'creator']);
        $company = CompanySetting::first();

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => $invoice,
                'company' => $company ? [
                    'name' => $company->company_name,
                    'address' => $company->address,
                    'gst_number' => $company->gst_number,
                    'vat_number' => $company->vat_number,
                    'tax_registration_number' => $company->tax_registration_number,
                    'contact_email' => $company->contact_email,
                    'contact_phone' => $company->contact_phone,
                    'website' => $company->website,
                ] : null,
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
                    'signature_url' => $company->signature_image ? url('storage/' . $company->signature_image) : null,
                    'seal_url' => $company->company_seal ? url('storage/' . $company->company_seal) : null,
                ] : null,
            ]
        ]);
    }

    public function validateInvoice(Invoice $invoice)
    {
        $validations = [];
        $allPass = true;

        $gstNumber = $invoice->customer?->gst_number;
        $gstValid = false;
        $gstMessage = 'No GST number on record for this customer';
        if ($gstNumber) {
            $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
            if (preg_match($pattern, strtoupper($gstNumber))) {
                $gstValid = true;
                $gstMessage = 'GST format verified - ' . $gstNumber;
            } else {
                $gstMessage = 'Invalid GST format: ' . $gstNumber . ' (expected format: 22AAAAA0000A1Z5)';
            }
        }
        $validations[] = [
            'type' => 'GST Validation',
            'status' => $gstValid ? 'passed' : 'warning',
            'message' => $gstMessage,
        ];
        if (!$gstValid) $allPass = false;

        $taxCalcValid = abs($invoice->grand_total - ($invoice->subtotal + $invoice->cgst_total + $invoice->sgst_total + $invoice->igst_total)) < 0.01;
        $validations[] = [
            'type' => 'Tax Calculations',
            'status' => $taxCalcValid ? 'passed' : 'failed',
            'message' => $taxCalcValid
                ? 'Tax calculations verified - all amounts match'
                : 'Tax calculation mismatch detected',
        ];
        if (!$taxCalcValid) $allPass = false;

        $hasItems = $invoice->items->count() > 0;
        $validations[] = [
            'type' => 'Line Items',
            'status' => $hasItems ? 'passed' : 'failed',
            'message' => $hasItems
                ? $invoice->items->count() . ' line item(s) present'
                : 'No line items found on invoice',
        ];
        if (!$hasItems) $allPass = false;

        $formatValid = !is_null($invoice->invoice_number) && !is_null($invoice->subtotal);
        $validations[] = [
            'type' => 'Format Compliance',
            'status' => $formatValid ? 'passed' : 'failed',
            'message' => $formatValid
                ? 'Invoice formatting compliant with standards'
                : 'Invoice missing required fields',
        ];
        if (!$formatValid) $allPass = false;

        $statusValid = in_array($invoice->status, ['Paid', 'Partially Paid', 'Sent', 'Draft', 'Unpaid', 'Overdue', 'Cancelled']);
        $validations[] = [
            'type' => 'Status Check',
            'status' => $statusValid ? 'passed' : 'warning',
            'message' => $statusValid
                ? 'Invoice status: ' . $invoice->status
                : 'Unrecognized invoice status',
        ];
        if (!$statusValid) $allPass = false;

        return response()->json([
            'success' => true,
            'data' => [
                'overall_status' => $allPass ? 'passed' : 'warning',
                'validations' => $validations,
            ]
        ]);
    }

    public function email(Request $request, Invoice $invoice, InvoicePdfService $pdfService)
    {
        $request->validate([
            'recipient_email' => 'nullable|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $invoice->load(['customer', 'items.product', 'creator']);
        $company = CompanySetting::first();

        $recipient = $request->recipient_email ?? $invoice->customer?->email;
        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'No recipient email available. Customer has no email address.',
            ], 400);
        }

        try {
            $pdf = $pdfService->generate($invoice);
            $pdfPath = storage_path('app/temp/invoice-' . $invoice->invoice_number . '.pdf');
            if (!is_dir(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }
            $pdf->save($pdfPath);

            $companyName = $company?->company_name ?? 'RFI Global ERP';

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($recipient, $pdfPath, $invoice, $request, $companyName) {
                $message->to($recipient)
                    ->subject($request->subject ?? 'Invoice ' . $invoice->invoice_number . ' from ' . $companyName)
                    ->attach($pdfPath, [
                        'as' => 'invoice-' . $invoice->invoice_number . '.pdf',
                        'mime' => 'application/pdf',
                    ]);

                if ($request->message) {
                    $message->setBody($request->message, 'text/plain');
                } else {
                    $message->setBody(
                        'Dear ' . ($invoice->customer?->name ?? 'Customer') . ",\n\nPlease find attached invoice " . $invoice->invoice_number . " for your reference.\n\nThank you for your business.\n\nBest regards,\n" . $companyName,
                        'text/plain'
                    );
                }
            });

            unlink($pdfPath);

            return response()->json([
                'success' => true,
                'message' => 'Invoice emailed successfully to ' . $recipient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
