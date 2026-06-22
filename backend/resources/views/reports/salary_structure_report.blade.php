<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{{ $report->name }}</title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#333;}
h1{font-size:18px;color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:6px;}
.meta{font-size:10px;color:#666;margin-bottom:16px;}
table{width:100%;border-collapse:collapse;margin-top:12px;}
th{background:#1a56db;color:#fff;padding:7px 8px;text-align:left;font-size:10px;text-transform:uppercase;}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;}
tr:nth-child(even) td{background:#f9fafb;}
.num{text-align:right;}
.s-card{display:inline-block;padding:10px 18px;margin-right:10px;margin-bottom:12px;border:1px solid #e5e7eb;border-radius:6px;min-width:100px;}
.s-card .n{font-size:20px;font-weight:700;color:#1a56db;}
.s-card .l{font-size:9px;color:#666;text-transform:uppercase;}
.footer{margin-top:20px;padding-top:10px;border-top:1px solid #e5e7eb;font-size:9px;color:#9ca3af;text-align:center;}
</style></head>
<body>
<h1>{{ $report->name }}</h1>
<p class="meta">{{ $report->description }}<br>
Generated: {{ $generated_at }} | By: {{ $generated_by }}
</p>

<div><div class="s-card"><div class="n">{{ $data['totalCount'] }}</div><div class="l">Active Structures</div></div></div>

<table>
<tr><th>Employee</th><th class="num">Basic (&#8377;)</th><th class="num">HRA (&#8377;)</th><th>Effective From</th><th>Effective To</th></tr>
@foreach($data['structures'] as $s)
<tr>
  <td>{{ $s->employee->full_name ?? $s->employee->name ?? 'N/A' }}</td>
  <td class="num">{{ number_format($s->basic_salary ?? 0, 0) }}</td>
  <td class="num">{{ number_format($s->hra ?? 0, 0) }}</td>
  <td>{{ $s->effective_from ? $s->effective_from->format('d-m-Y') : '-' }}</td>
  <td>{{ $s->effective_to ? $s->effective_to->format('d-m-Y') : '-' }}</td>
</tr>
@endforeach
</table>

<div class="footer">RFI Management Suite &mdash; Payroll &mdash; Confidential</div>
</body>
</html>
