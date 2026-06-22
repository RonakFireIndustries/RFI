<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; line-height: 1.5; }
        .invoice-box { max-width: 190mm; margin: 0 auto; padding: 20px 25px; }
        .header-table { width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 15px; }
        .company-name { font-size: 20px; font-weight: bold; color: #2563eb; }
        .company-details { font-size: 10px; color: #555; margin-top: 3px; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #e5e7eb; letter-spacing: 3px; text-transform: uppercase; text-align: right; }
        .invoice-meta { text-align: right; font-size: 10px; color: #555; margin-top: 5px; }
        .invoice-meta strong { color: #333; }
        .section-title { font-size: 9px; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .billing-table { width: 100%; margin-bottom: 20px; }
        .billing-table td { vertical-align: top; width: 50%; }
        .customer-name { font-size: 13px; font-weight: bold; color: #111; }
        .customer-detail { font-size: 10px; color: #555; margin-top: 2px; }
        .gst-badge { display: inline-block; background: #f3f4f6; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; color: #374151; margin-top: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background: #f9fafb; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; padding: 8px 6px; font-size: 9px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        .items-table th.right { text-align: right; }
        .items-table th.center { text-align: center; }
        .items-table td { padding: 7px 6px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
        .items-table td.right { text-align: right; }
        .items-table td.center { text-align: center; }
        .items-table tr:last-child td { border-bottom: 1px solid #e5e7eb; }
        .item-desc { font-weight: 600; color: #111; }
        .item-sku { font-size: 9px; color: #9ca3af; }
        .totals-wrapper { width: 100%; margin-bottom: 20px; }
        .totals-left { float: left; width: 50%; }
        .totals-right { float: right; width: 45%; }
        .totals-table { width: 100%; }
        .totals-table td { padding: 3px 0; text-align: right; font-size: 11px; }
        .totals-table td.label { text-align: left; color: #6b7280; }
        .totals-table .grand-total td { font-weight: bold; font-size: 14px; color: #2563eb; border-top: 2px solid #2563eb; padding-top: 6px; }
        .totals-table .paid td { color: #059669; }
        .totals-table .balance td { color: #dc2626; font-weight: bold; }
        .notes-section { font-size: 10px; color: #6b7280; margin-bottom: 10px; }
        .notes-section strong { color: #374151; }
        .bank-details { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px; margin-bottom: 15px; width: 55%; }
        .bank-details h4 { margin: 0 0 6px 0; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; }
        .bank-details p { margin: 2px 0; font-size: 10px; color: #374151; }
        .signature-section { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
        .signature-line { margin-top: 40px; width: 200px; border-top: 1px solid #333; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }
        .clear { clear: both; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 9px; font-weight: bold; background: #dbeafe; color: #1e40af; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        @page { margin: 15mm 10mm; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <div class="company-name">{{ $company->company_name ?? 'RFI Global ERP' }}</div>
                    @if($company && $company->address)
                    <div class="company-details">{{ $company->address }}</div>
                    @endif
                    @if($company && $company->gst_number)
                    <div class="company-details">GST: {{ $company->gst_number }}</div>
                    @endif
                    @if($company && $company->contact_email)
                    <div class="company-details">{{ $company->contact_email }} | {{ $company->contact_phone ?? '' }}</div>
                    @endif
                </td>
                <td style="width: 40%;">
                    <div class="invoice-title">Invoice</div>
                    <div class="invoice-meta">
                        <strong>#{{ $invoice->invoice_number }}</strong><br>
                        Date: {{ $invoice->created_at->format('d M, Y') }}<br>
                        @if($invoice->due_date)
                        Due Date: <strong>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') }}</strong><br>
                        @endif
                        Status: <span class="status-badge {{ $invoice->status === 'Paid' ? 'status-paid' : ($invoice->status === 'Overdue' ? 'status-overdue' : '') }}">{{ $invoice->status }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="billing-table">
            <tr>
                <td>
                    <div class="section-title">Bill To</div>
                    <div class="customer-name">{{ $customer->name ?? 'N/A' }}</div>
                    @if($customer && $customer->address)
                    <div class="customer-detail">{{ $customer->address }}</div>
                    @endif
                    <div class="customer-detail">{{ $customer->email ?? '' }}</div>
                    <div class="customer-detail">{{ $customer->phone ?? '' }}</div>
                    @if($customer && $customer->gst_number)
                    <div class="gst-badge">GSTIN: {{ $customer->gst_number }}</div>
                    @endif
                </td>
                <td style="text-align: right;">
                    @if($invoice->salesOrder && $invoice->salesOrder->shipping_address)
                    <div class="section-title">Ship To</div>
                    <div class="customer-detail">{{ $invoice->salesOrder->shipping_address }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 36%;">Description</th>
                    <th style="width: 12%;" class="center">HSN/SKU</th>
                    <th style="width: 8%;" class="center">Qty</th>
                    <th style="width: 12%;" class="right">Rate</th>
                    <th style="width: 8%;" class="right">Disc%</th>
                    <th style="width: 12%;" class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <span class="item-desc">{{ $item->item_description }}</span>
                        @if($item->product && $item->product->sku)
                        <br><span class="item-sku">{{ $item->product->sku }}</span>
                        @endif
                    </td>
                    <td class="center">{{ $item->hsn_code ?? ($item->product->hsn_code ?? '-') }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ $item->discount > 0 ? number_format($item->discount, 2) : '-' }}</td>
                    <td class="right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af; padding: 20px;">No line items found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals-wrapper">
            <div class="totals-left">
                @if($invoice->notes)
                <div class="notes-section"><strong>Notes:</strong><br>{{ $invoice->notes }}</div>
                @endif
                @if($invoice->terms)
                <div class="notes-section"><strong>Terms & Conditions:</strong><br>{{ $invoice->terms }}</div>
                @endif
            </div>
            <div class="totals-right">
                <table class="totals-table">
                    <tr>
                        <td class="label">Sub Total</td>
                        <td>₹{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->cgst_total > 0)
                    <tr>
                        <td class="label">CGST</td>
                        <td>₹{{ number_format($invoice->cgst_total, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->sgst_total > 0)
                    <tr>
                        <td class="label">SGST</td>
                        <td>₹{{ number_format($invoice->sgst_total, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->igst_total > 0)
                    <tr>
                        <td class="label">IGST</td>
                        <td>₹{{ number_format($invoice->igst_total, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="label">Grand Total</td>
                        <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                    @if($invoice->paid_amount > 0)
                    <tr class="paid">
                        <td class="label">Amount Paid</td>
                        <td>₹{{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="balance">
                        <td class="label">Balance Due</td>
                        <td>₹{{ number_format($invoice->grand_total - $invoice->paid_amount, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="clear"></div>
        </div>

        @if($bank_details)
        <div class="bank-details">
            <h4>Bank Details</h4>
            <p><strong>Bank:</strong> {{ $bank_details['bank_name'] }}</p>
            <p><strong>Account Name:</strong> {{ $bank_details['account_name'] }}</p>
            <p><strong>Account No:</strong> {{ $bank_details['account_number'] }}</p>
            <p><strong>IFSC:</strong> {{ $bank_details['ifsc_code'] }} @if($bank_details['swift_code']) | <strong>SWIFT:</strong> {{ $bank_details['swift_code'] }} @endif</p>
            <p><strong>Branch:</strong> {{ $bank_details['branch'] }}</p>
        </div>
        @endif

        @if($signature)
        <div class="signature-section">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <p style="font-size: 10px; color: #6b7280; margin: 0;">Authorised Signatory</p>
                        <div class="signature-line"></div>
                        <p style="font-size: 11px; font-weight: bold; margin: 5px 0 0 0;">{{ $signature['signatory_name'] }}</p>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <p style="font-size: 10px; color: #6b7280; margin: 0;">For {{ $company->company_name ?? 'RFI Global ERP' }}</p>
                    </td>
                </tr>
            </table>
        </div>
        @endif

        <div class="footer">
            <p>Generated on {{ $generated_at ?? now()->format('d M Y, h:i A') }} by {{ $generated_by ?? 'System' }} | ERP Ref: INV-{{ $invoice->invoice_number }}</p>
            <p>This is a computer-generated invoice and does not require a physical signature. | Subject to {{ $company->city ?? 'Mumbai' }} jurisdiction</p>
        </div>
    </div>
</body>
</html>
