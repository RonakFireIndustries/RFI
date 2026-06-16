<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #1a56db; }
        .invoice-details { text-align: right; }
        .billing-info { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        .table th { background-color: #f8f9fa; }
        .table .right { text-align: right; }
        .totals { width: 50%; float: right; }
        .totals-table { width: 100%; }
        .totals-table td { padding: 5px; text-align: right; }
        .totals-table .grand-total { font-weight: bold; font-size: 18px; border-top: 2px solid #333; padding-top: 10px; }
        .clear { clear: both; }
        .footer { margin-top: 50px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div style="width: 100%; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 24px; font-weight: bold; color: #1a56db;">RFI Global ERP</td>
                <td style="text-align: right;">
                    <h2 style="margin: 0; color: #555;">INVOICE</h2>
                    <p style="margin: 5px 0 0 0;">#{{ $invoice->invoice_number }}</p>
                    <p style="margin: 5px 0 0 0; color: #777;">Date: {{ $invoice->created_at->format('M d, Y') }}</p>
                    @if($invoice->due_date)
                    <p style="margin: 5px 0 0 0; color: #777;">Due Date: {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <h4 style="margin: 0 0 10px 0; color: #555;">Bill To:</h4>
                <p style="margin: 0; font-weight: bold;">{{ $customer->name ?? 'N/A' }}</p>
                <p style="margin: 5px 0 0 0; color: #777;">{{ $customer->email ?? '' }}</p>
                <p style="margin: 5px 0 0 0; color: #777;">{{ $customer->phone ?? '' }}</p>
                @if($customer && $customer->gst_number)
                <p style="margin: 5px 0 0 0; color: #777;">GSTIN: {{ $customer->gst_number }}</p>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <h4 style="margin: 0 0 10px 0; color: #555;">From:</h4>
                <p style="margin: 0; font-weight: bold;">{{ $branch->name ?? 'Main Branch' }}</p>
                <p style="margin: 5px 0 0 0; color: #777;">{{ $branch->address ?? '' }}</p>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Item Description</th>
                <th>HSN/SAC</th>
                <th class="right">Qty</th>
                <th class="right">Rate</th>
                <th class="right">Tax Rate</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    {{ $item->item_description }}
                    @if($item->product)
                    <br><small style="color: #777;">{{ $item->product->sku }}</small>
                    @endif
                </td>
                <td>{{ $item->hsn_code ?? '-' }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="right">{{ number_format($item->tax_rate, 2) }}%</td>
                <td class="right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="width: 100%;">
        <div style="float: left; width: 50%;">
            @if($invoice->notes)
            <h5 style="margin: 0 0 5px 0;">Notes:</h5>
            <p style="font-size: 12px; color: #555;">{{ $invoice->notes }}</p>
            @endif
            @if($invoice->terms)
            <h5 style="margin: 15px 0 5px 0;">Terms & Conditions:</h5>
            <p style="font-size: 12px; color: #555;">{{ $invoice->terms }}</p>
            @endif
        </div>
        <div style="float: right; width: 40%;">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>₹{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->cgst_total > 0)
                <tr>
                    <td>CGST:</td>
                    <td>₹{{ number_format($invoice->cgst_total, 2) }}</td>
                </tr>
                @endif
                @if($invoice->sgst_total > 0)
                <tr>
                    <td>SGST:</td>
                    <td>₹{{ number_format($invoice->sgst_total, 2) }}</td>
                </tr>
                @endif
                @if($invoice->igst_total > 0)
                <tr>
                    <td>IGST:</td>
                    <td>₹{{ number_format($invoice->igst_total, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="grand-total">Total Amount:</td>
                    <td class="grand-total">₹{{ number_format($invoice->grand_total, 2) }}</td>
                </tr>
                @if($invoice->paid_amount > 0)
                <tr>
                    <td>Amount Paid:</td>
                    <td>₹{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Balance Due:</td>
                    <td style="font-weight: bold;">₹{{ number_format($invoice->grand_total - $invoice->paid_amount, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <div class="footer">
        <p>This is a computer generated invoice and requires no signature.</p>
    </div>
</body>
</html>
