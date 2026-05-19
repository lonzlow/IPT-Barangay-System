<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Residents List Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #1a56db;
        }

        .header p {
            margin: 5px 0;
            font-size: 10px;
            color: #666;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #1a56db;
            color: white;
        }

        th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f0f0f0;
        }

        .status {
            padding: 3px 6px;
            border-radius: 3px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }

        .status-active {
            background-color: #dcfce7;
            color: #15803d;
        }

        .status-deceased {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .status-transferred {
            background-color: #fef3c7;
            color: #b45309;
        }

        .voter-registered {
            background-color: #dcfce7;
            color: #15803d;
        }

        .voter-unregistered {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .voter-suspended {
            background-color: #fef3c7;
            color: #b45309;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .total-row {
            background-color: #eff6ff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BARANGAY RESIDENT LIST</h1>
        <p>Comprehensive Population Report</p>
    </div>

    <div class="meta">
        <div>
            <strong>Report Generated:</strong> {{ $generatedAt }}
        </div>
        <div>
            <strong>Total Residents:</strong> {{ $residents->count() }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">Name</th>
                <th width="8%">Age</th>
                <th width="8%">Gender</th>
                <th width="15%">Email</th>
                <th width="12%">Contact</th>
                <th width="12%">Address</th>
                <th width="10%">Civil Status</th>
                <th width="10%">Voter Status</th>
                <th width="10%">Residency Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($residents as $index => $resident)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $resident->first_name }}
                        @if($resident->middle_name)
                            {{ substr($resident->middle_name, 0, 1) }}.
                        @endif
                        {{ $resident->last_name }}
                        @if($resident->suffix)
                            {{ $resident->suffix }}
                        @endif
                    </td>
                    <td>{{ $resident->age ?? 'N/A' }}</td>
                    <td>{{ $resident->gender }}</td>
                    <td>{{ $resident->email }}</td>
                    <td>{{ $resident->contact_number }}</td>
                    <td>
                        @if($resident->household)
                            {{ $resident->household->house_number }} {{ $resident->household->street }}<br>
                            <small>{{ $resident->household->purok->purok_name ?? 'N/A' }}</small>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $resident->civil_status }}</td>
                    <td>
                        <span class="status @if($resident->voter_status === 'Registered') voter-registered @elseif($resident->voter_status === 'Unregistered') voter-unregistered @else voter-suspended @endif">
                            {{ $resident->voter_status }}
                        </span>
                    </td>
                    <td>
                        <span class="status @if($resident->residency_status === 'Active') status-active @elseif($resident->residency_status === 'Deceased') status-deceased @else status-transferred @endif">
                            {{ $resident->residency_status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #999;">No residents found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="10">Total Residents: {{ $residents->count() }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This is an official report generated by the Barangay Management System.</p>
    </div>
</body>
</html>
