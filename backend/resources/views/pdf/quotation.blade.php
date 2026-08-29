<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BOQ {{ $quotation->quotation_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #1f2937; font-size: 12px; margin: 0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1e40af; padding-bottom: 12px; margin-bottom: 16px; }
        .brand h1 { margin: 0; font-size: 20px; color: #1e40af; }
        .brand p { margin: 2px 0; color: #6b7280; font-size: 11px; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 18px; color: #1f2937; text-transform: uppercase; }
        .doc-title .no { font-weight: bold; color: #1e40af; }
        .meta { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta td { padding: 4px 8px; }
        .meta td.label { width: 140px; color: #6b7280; font-weight: 600; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { background: #1e40af; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
        table.items td { border: 1px solid #e5e7eb; padding: 8px; }
        table.items tr:nth-child(even) td { background: #f9fafb; }
        table.items tr.section-name td, table.items tr.section-total td { background: #eef2ff; }
        table.items td.section-name { font-weight: bold; color: #1e40af; text-transform: uppercase; letter-spacing: .03em; }
        .num { text-align: right; }
        .totals { width: 260px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 5px 8px; }
        .totals .grand td { font-size: 14px; font-weight: bold; color: #1e40af; border-top: 2px solid #1e40af; }
        .terms { margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 10px; font-size: 11px; color: #374151; }
        .terms h4 { margin: 0 0 4px; color: #1e40af; }
        .footer { margin-top: 24px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            <h1>Ronak Fire Industries</h1>
            <p>Fire Safety &amp; Firefighting Systems</p>
        </div>
        <div class="doc-title">
            <h2>Bill of Quantities</h2>
            <div class="no">{{ $quotation->quotation_no }}</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Building / Site</td>
            <td>{{ $quotation->display_building_name ?? '—' }}</td>
            <td class="label">Quotation Date</td>
            <td>{{ $quotation->quotation_date?->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td>{{ ucfirst($quotation->status) }}</td>
            <td class="label">Prepared By</td>
            <td>{{ $quotation->creator?->name ?? '—' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Description</th>
                <th style="width:60px">Unit</th>
                <th class="num" style="width:70px">Qty</th>
                <th class="num" style="width:90px">Rate</th>
                <th class="num" style="width:100px">Amount</th>
            </tr>
        </thead>
    </table>

    @php $running = 0; @endphp
    @foreach ($sections as $section)
        @php
            $sub = round($section->items->sum('amount'), 2);
            $running += $sub;
        @endphp
        <table class="items section">
            <tbody>
                <tr>
                    <td colspan="6" class="section-name">{{ $section->name }}</td>
                </tr>
                @foreach ($section->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->description ?? '—' }}</td>
                        <td>{{ $item->unit ?? '—' }}</td>
                        <td class="num">{{ rtrim(rtrim(number_format((float)$item->qty, 2), '0'), '.') }}</td>
                        <td class="num">{{ number_format((float)$item->rate, 2) }}</td>
                        <td class="num">{{ number_format((float)$item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="section-total">
                    <td colspan="5" style="text-align:right; font-weight:bold;">{{ $section->name }} Subtotal</td>
                    <td class="num" style="font-weight:bold;">{{ number_format($sub, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="num">{{ number_format($running, 2) }}</td>
        </tr>
        @if ((float)$quotation->discount > 0)
            <tr>
                <td>Discount</td>
                <td class="num">- {{ number_format((float)$quotation->discount, 2) }}</td>
            </tr>
        @endif
        @if ((float)$quotation->gst_percent > 0)
            <tr>
                <td>GST ({{ rtrim(rtrim(number_format((float)$quotation->gst_percent, 2), '0'), '.') }}%)</td>
                <td class="num">{{ number_format(((float)$quotation->grand_total) - (((float)$quotation->subtotal) - (float)$quotation->discount), 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td>Grand Total</td>
            <td class="num">{{ number_format($quotation->grand_total, 2) }}</td>
        </tr>
    </table>

    @if ($quotation->terms)
        <div class="terms">
            <h4>Terms &amp; Conditions</h4>
            <div>{!! nl2br(e($quotation->terms)) !!}</div>
        </div>
    @endif

    <div class="footer">Generated on {{ now()->format('d-m-Y H:i') }} · Ronak Fire Industries</div>
</body>
</html>
