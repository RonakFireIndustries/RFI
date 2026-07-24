<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Building Report - {{ $building->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1 { color: #1a1a1a; border-bottom: 2px solid #dc2626; padding-bottom: 8px; font-size: 22px; }
        h2 { color: #374151; margin-top: 20px; font-size: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #d1d5db; padding: 6px 10px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .section { margin-bottom: 20px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; color: white; }
        .badge-red { background-color: #dc2626; }
        .badge-green { background-color: #16a34a; }
        .badge-yellow { background-color: #eab308; color: #000; }
        .badge-blue { background-color: #2563eb; }
        .header-meta { color: #6b7280; font-size: 11px; margin-top: 5px; }
    </style>
</head>
<body>
    <h1>{{ $building->name }}</h1>
    <p class="header-meta">Report generated: {{ now()->format('d M Y H:i') }}</p>
    @if($building->society_name)
        <p><strong>Society:</strong> {{ $building->society_name }}</p>
    @endif

    <div class="section">
        <h2>Basic Information</h2>
        <table>
            <tr><th>Address</th><td>{{ $building->address ?? '-' }}</td></tr>
            <tr><th>Area</th><td>{{ $building->area ?? '-' }}</td></tr>
            <tr><th>City</th><td>{{ $building->city ?? '-' }}</td></tr>
            <tr><th>State</th><td>{{ $building->state ?? '-' }}</td></tr>
            <tr><th>Pincode</th><td>{{ $building->pincode ?? '-' }}</td></tr>
            <tr><th>Building Type</th><td>{{ $building->building_type ?? '-' }}</td></tr>
            <tr><th>Status</th><td>{{ $building->status }}</td></tr>
            <tr><th>Floors</th><td>{{ $building->no_of_floors ?? '-' }}</td></tr>
            <tr><th>Flats</th><td>{{ $building->no_of_flats ?? '-' }}</td></tr>
            <tr><th>Construction Year</th><td>{{ $building->construction_year ?? '-' }}</td></tr>
            <tr><th>Fire NOC Status</th><td>{{ $building->fire_noc_status ?? '-' }}</td></tr>
            <tr><th>Under Construction</th><td>{{ $building->under_construction ? 'Yes' : 'No' }}</td></tr>
        </table>
    </div>

    @if($building->statuses->count())
    <div class="section">
        <h2>Statuses</h2>
        @foreach($building->statuses as $status)
            <span class="badge" style="background-color: {{ $status->color }}">{{ $status->name }}</span>
        @endforeach
    </div>
    @endif

    @if($building->wings->count())
    <div class="section">
        <h2>Wings</h2>
        <table>
            <tr><th>Name</th><th>Floors</th><th>Flats/Floor</th><th>Config</th><th>Total</th></tr>
            @foreach($building->wings as $wing)
            <tr>
                <td>{{ $wing->name }}</td>
                <td>{{ $wing->floors }}</td>
                <td>{{ $wing->flats_per_floor }}</td>
                <td>{{ $wing->flat_configuration ?? '-' }}</td>
                <td>{{ $wing->total_flats }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    @if($building->fireSystems->count())
    <div class="section">
        <h2>Fire Systems</h2>
        <table>
            <tr><th>Type</th><th>Sub Type</th><th>Qty</th><th>Brand</th><th>Capacity</th><th>Installed</th><th>Last Tested</th></tr>
            @foreach($building->fireSystems as $system)
            <tr>
                <td>{{ $system->system_type }}</td>
                <td>{{ $system->sub_type ?? '-' }}</td>
                <td>{{ $system->quantity ?? '-' }}</td>
                <td>{{ $system->brand ?? '-' }}</td>
                <td>{{ $system->capacity ?? '-' }}</td>
                <td>{{ $system->installation_year ?? '-' }}</td>
                <td>{{ $system->last_testing_date?->format('d M Y') ?? '-' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    @if($building->contacts->count())
    <div class="section">
        <h2>Contacts</h2>
        <table>
            <tr><th>Name</th><th>Role</th><th>Category</th><th>Mobile</th><th>WhatsApp</th><th>Email</th></tr>
            @foreach($building->contacts as $contact)
            <tr>
                <td>{{ $contact->full_name }}</td>
                <td>{{ $contact->role }}</td>
                <td>{{ ucfirst($contact->role_category) }}</td>
                <td>{{ $contact->mobile_number ?? '-' }}</td>
                <td>{{ $contact->whatsapp_number ?? '-' }}</td>
                <td>{{ $contact->email ?? '-' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div class="section">
        <h2>Key Stakeholders</h2>
        <table>
            <tr><th>Developer</th><td>{{ $building->developer_name ?? '-' }} {{ $building->developer_contact ? "({$building->developer_contact})" : '' }}</td></tr>
            <tr><th>Architect</th><td>{{ $building->architect_name ?? '-' }} {{ $building->architect_contact ? "({$building->architect_contact})" : '' }}</td></tr>
            <tr><th>PMC Consultant</th><td>{{ $building->pmc_consultant_name ?? '-' }} {{ $building->pmc_consultant_contact ? "({$building->pmc_consultant_contact})" : '' }}</td></tr>
            <tr><th>Property Owner</th><td>{{ $building->property_owner ?? '-' }}</td></tr>
        </table>
    </div>
</body>
</html>
