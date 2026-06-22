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
.footer{margin-top:20px;padding-top:10px;border-top:1px solid #e5e7eb;font-size:9px;color:#9ca3af;text-align:center;}
</style></head>
<body>
<h1>{{ $report->name }}</h1>
<p class="meta">{{ $report->description }}<br>
Generated: {{ $generated_at }} | By: {{ $generated_by }}
@if(!empty($params['start_date'])) | From: {{ $params['start_date'] }} @endif
@if(!empty($params['end_date'])) | To: {{ $params['end_date'] }} @endif
</p>

<table>
<tr><th>Employee</th><th class="num">Days</th><th class="num">Total Hours</th><th>Status</th></tr>
@foreach($data['records'] as $name => $rows)
@foreach($rows as $r)
<tr>
  <td>{{ $name }}</td>
  <td class="num">{{ $r->total_days }}</td>
  <td class="num">{{ number_format($r->total_hours ?? 0, 1) }}</td>
  <td>{{ $r->status ?? 'N/A' }}</td>
</tr>
@endforeach
@endforeach
</table>

<div class="footer">RFI Management Suite &mdash; Attendance &mdash; Confidential</div>
</body>
</html>
