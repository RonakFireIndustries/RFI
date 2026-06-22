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
@if(!empty($params['start_date'])) | From: {{ $params['start_date'] }} @endif
@if(!empty($params['end_date'])) | To: {{ $params['end_date'] }} @endif
</p>

<div>
  <div class="s-card"><div class="n">{{ $data['summary']['total_employees'] }}</div><div class="l">Employees</div></div>
  <div class="s-card"><div class="n">{{ number_format($data['summary']['total_allocated'], 1) }}</div><div class="l">Total Allocated</div></div>
  <div class="s-card"><div class="n">{{ number_format($data['summary']['total_used'], 1) }}</div><div class="l">Total Used</div></div>
  <div class="s-card"><div class="n">{{ number_format($data['summary']['total_remaining'], 1) }}</div><div class="l">Total Remaining</div></div>
</div>

<table>
<tr><th>Employee</th><th>Leave Type</th><th class="num">Allocated</th><th class="num">Used</th><th class="num">Remaining</th><th>Year</th></tr>
@foreach($data['balances'] as $b)
<tr>
  <td>{{ $b->employee->full_name ?? $b->employee->name ?? 'N/A' }}</td>
  <td>{{ $b->leaveType->name ?? 'N/A' }}</td>
  <td class="num">{{ number_format($b->allocated, 1) }}</td>
  <td class="num">{{ number_format($b->used, 1) }}</td>
  <td class="num">{{ number_format($b->remaining, 1) }}</td>
  <td>{{ $b->year }}</td>
</tr>
@endforeach
</table>

<div class="footer">RFI Management Suite &mdash; Leaves &mdash; Confidential</div>
</body>
</html>
