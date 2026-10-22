<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $payload['report']['label'] ?? 'Report' }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
  h1 { font-size: 17px; color: #1a56db; margin-bottom: 2px; }
  .subtitle { color: #666; font-size: 10px; margin-bottom: 12px; }
  .meta { font-size: 9px; color: #666; margin-bottom: 14px; }
  .meta span { margin-right: 18px; }
  .summary { margin-bottom: 14px; }
  .summary-card { display: inline-block; padding: 7px 14px; margin: 0 8px 8px 0; border: 1px solid #e5e7eb; border-radius: 5px; text-align: center; }
  .summary-card .num { font-size: 14px; font-weight: 700; color: #1a56db; }
  .summary-card .lbl { font-size: 8px; color: #666; text-transform: uppercase; }
  table { width: 100%; border-collapse: collapse; }
  thead { display: table-header-group; }
  th { background: #1a56db; color: #fff; padding: 6px 7px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 5px 7px; border-bottom: 1px solid #e5e7eb; }
  tr:nth-child(even) td { background: #f9fafb; }
  .num { text-align: right; }
  .footer { margin-top: 18px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: center; }
  .empty { padding: 18px 0; text-align: center; color: #9ca3af; }
</style>
</head>
<body>
  <h1>{{ $payload['report']['label'] ?? 'Report' }}</h1>
  @if(!empty($payload['report']['description']))
    <p class="subtitle">{{ $payload['report']['description'] }}</p>
  @endif

  <div class="meta">
    <span>Module: {{ $payload['report']['module_label'] ?? '' }}</span>
    <span>Generated: {{ $generated_at }}</span>
    <span>By: {{ $generated_by }}</span>
    <span>Rows: {{ $payload['meta']['returned'] ?? 0 }} of {{ $payload['meta']['total'] ?? 0 }}</span>
    @if(!empty($payload['filters']))
      <span>Filters: {{ collect($payload['filters'])->map(fn($v, $k) => $k . '=' . $v)->implode('; ') }}</span>
    @endif
  </div>

  @if(!empty($payload['summary']))
    <div class="summary">
      @foreach($payload['summary'] as $figure)
        <div class="summary-card">
          <div class="num">{{ $format($figure['value'], $figure['format'] ?? 'number') }}</div>
          <div class="lbl">{{ $figure['label'] }}</div>
        </div>
      @endforeach
    </div>
  @endif

  <table>
    <thead>
      <tr>
        @foreach($payload['report']['columns'] as $column)
          <th class="{{ $column['align'] === 'right' ? 'num' : '' }}">{{ $column['label'] }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse($payload['rows'] as $row)
        <tr>
          @foreach($payload['report']['columns'] as $column)
            <td class="{{ $column['align'] === 'right' ? 'num' : '' }}">{{ $format($row[$column['key']] ?? null, $column['format'] ?? 'text') }}</td>
          @endforeach
        </tr>
      @empty
        <tr><td colspan="{{ count($payload['report']['columns']) }}" class="empty">No rows matched the selected filters.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="footer">RFI Management Suite &mdash; {{ $payload['report']['module_label'] ?? 'Reports' }} &mdash; Confidential</div>
</body>
</html>
