<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Role & Permission Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 16px; }
        h1 { color: #1a1a1a; border-bottom: 2px solid #2563eb; padding-bottom: 8px; font-size: 20px; }
        h2 { color: #1f2937; margin: 22px 0 8px; font-size: 14px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        h3 { color: #374151; font-size: 12px; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        th, td { border: 1px solid #d1d5db; padding: 4px 7px; text-align: left; }
        th { background-color: #eff6ff; font-weight: bold; }
        .header-meta { color: #6b7280; font-size: 10px; margin-top: 4px; }
        .summary-table { margin-top: 14px; }
        .check { color: #16a34a; font-weight: bold; }
        .cross { color: #9ca3af; }
        .count-badge { display: inline-block; background: #2563eb; color: #fff; border-radius: 10px; padding: 1px 8px; font-size: 10px; }
        .access-badge { display: inline-block; background: #d1fae5; color: #065f46; border-radius: 10px; padding: 1px 8px; font-size: 10px; }
        .role-section { page-break-inside: auto; }
        .module-name { font-weight: bold; }
        .other-list { color: #4b5563; font-size: 10px; }
        .footer { margin-top: 20px; color: #9ca3af; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <h1>Role &amp; Permission Report</h1>
    <p class="header-meta">Generated: {{ $generated_at }} &nbsp;|&nbsp; Total roles: {{ $roles->count() }}</p>

    <table class="summary-table">
        <tr>
            <th>Role</th>
            <th>Guard</th>
            <th>Permissions</th>
            <th>Access Level</th>
        </tr>
        @foreach($roles as $role)
        <tr>
            <td><strong>{{ $role['name'] }}</strong></td>
            <td>{{ $role['guard'] }}</td>
            <td>{{ $role['count'] }}</td>
            <td><span class="access-badge">{{ $role['access_level'] }}</span></td>
        </tr>
        @endforeach
    </table>

    @foreach($roles as $role)
    <div class="role-section">
        <h2>
            {{ $role['name'] }}
            <span class="count-badge">{{ $role['count'] }} permission(s)</span>
        </h2>

        <table>
            <tr>
                <th style="width: 22%;">Module</th>
                <th class="text-center" style="width: 14%;">View</th>
                <th class="text-center" style="width: 14%;">Create</th>
                <th class="text-center" style="width: 14%;">Edit</th>
                <th class="text-center" style="width: 14%;">Delete</th>
                <th>Other Permissions</th>
            </tr>
            @forelse($role['modules'] as $module => $flags)
            <tr>
                <td class="module-name">{{ ucwords(str_replace('_', ' ', $module)) }}</td>
                <td class="text-center">{{ $flags['view'] ? '<span class="check">&#10003;</span>' : '<span class="cross">&#10007;</span>' }}</td>
                <td class="text-center">{{ $flags['create'] ? '<span class="check">&#10003;</span>' : '<span class="cross">&#10007;</span>' }}</td>
                <td class="text-center">{{ $flags['edit'] ? '<span class="check">&#10003;</span>' : '<span class="cross">&#10007;</span>' }}</td>
                <td class="text-center">{{ $flags['delete'] ? '<span class="check">&#10003;</span>' : '<span class="cross">&#10007;</span>' }}</td>
                <td class="other-list">
                    {{ implode(', ', $flags['other']) ?: '-' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="color:#6b7280;">No permissions assigned.</td></tr>
            @endforelse
        </table>
    </div>
    @endforeach

    <div class="footer">RFI Management Suite &mdash; Role &amp; Permission Configuration Report</div>
</body>
</html>
