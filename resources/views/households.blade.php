{{--
    households.blade.php
    Route: GET /households  →  route('households.index')
--}}
@extends('layouts.app')

@section('title', 'Households & Purok – Barangay Management System')
@section('page-title', 'Household & Purok Management')

@section('styles')
<style>
    .si-blue   { background: #eff6ff; color: #1a56db; }
    .si-violet { background: #f5f3ff; color: #7c3aed; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-teal   { background: #f0fdfa; color: #0d9488; }

    .purok-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 18px 20px; transition: box-shadow .2s;
    }
    .purok-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.07); }
    .purok-card .pk-name  { font-size: 14px; font-weight: 800; color: #0f172a; }
    .purok-card .pk-leader{ font-size: 12px; color: #64748b; margin-top: 2px; }
    .purok-card .pk-stats { display: flex; gap: 16px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9; }
    .purok-card .pk-stat-val { font-size: 20px; font-weight: 800; font-family: 'DM Mono',monospace; color: #0f172a; line-height: 1; }
    .purok-card .pk-stat-lbl { font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 2px; }
    .pk-badge { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }

    .chart-wrap { position: relative; height: 220px; }
</style>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Household & Purok Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Manage household records and purok zone assignments.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13px;font-weight:600;padding:8px 16px;">
            <i class="bi bi-person-badge-fill"></i> Assign Purok Leader
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Add Household
        </button>
    </div>
</div>

{{-- ── STAT WIDGETS ── --}}
<div class="section-heading">Overview</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-blue"><i class="bi bi-houses-fill"></i></div>
            <div><div class="stat-value">1,204</div><div class="stat-label">Total Households</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-violet"><i class="bi bi-signpost-fill"></i></div>
            <div><div class="stat-value">8</div><div class="stat-label">Total Puroks</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-green"><i class="bi bi-person-check-fill"></i></div>
            <div><div class="stat-value">8</div><div class="stat-label">Purok Leaders Assigned</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-teal"><i class="bi bi-people-fill"></i></div>
            <div><div class="stat-value">4.0</div><div class="stat-label">Avg. Family Size</div></div>
        </div>
    </div>
</div>

{{-- ── PUROK OVERVIEW CARDS ── --}}
<div class="section-heading">Purok Leaders Overview</div>
<div class="row g-3 mb-4">
    @php
    $puroks = [
        ['Purok 1 – Mabini',    'Maria Santos',    142, 571, '#1a56db'],
        ['Purok 2 – Rizal',     'Juan dela Cruz',  158, 620, '#7c3aed'],
        ['Purok 3 – Bonifacio', 'Ana Reyes',       121, 486, '#0d9488'],
        ['Purok 4 – Luna',      'Carlo Mendoza',   168, 680, '#ea580c'],
        ['Purok 5 – Aguinaldo', 'Rosa Aquino',     145, 584, '#16a34a'],
        ['Purok 6 – Burgos',    'Ernesto Lim',     133, 535, '#db2777'],
        ['Purok 7 – Jacinto',   'Luz Villanueva',  188, 749, '#d97706'],
        ['Purok 8 – Osmeña',    'Pedro Garcia',    149, 596, '#0369a1'],
    ];
    @endphp
    @foreach($puroks as $p)
    <div class="col-md-6 col-xl-3">
        <div class="purok-card">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="pk-badge" style="background:{{ $p[4] }}1a;color:{{ $p[4] }};"><i class="bi bi-geo-alt-fill"></i></div>
                <div>
                    <div class="pk-name">{{ $p[0] }}</div>
                    <div class="pk-leader"><i class="bi bi-person-fill me-1"></i>{{ $p[1] }}</div>
                </div>
            </div>
            <div class="pk-stats">
                <div><div class="pk-stat-val">{{ $p[2] }}</div><div class="pk-stat-lbl">Households</div></div>
                <div><div class="pk-stat-val">{{ $p[3] }}</div><div class="pk-stat-lbl">Residents</div></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── CHARTS ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="chart-card">
            <div class="card-heading">Voter Statistics per Purok</div>
            <div class="card-sub mb-3">Registered voters vs. total 18+ residents</div>
            <div class="chart-wrap"><canvas id="voterPurokChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="chart-card">
            <div class="card-heading">Family Size Distribution</div>
            <div class="card-sub mb-3">Number of households per family size</div>
            <div class="chart-wrap"><canvas id="familySizeChart"></canvas></div>
        </div>
    </div>
</div>

{{-- ── HOUSEHOLD TABLE ── --}}
<div class="section-heading">Household Records</div>
<div class="d-flex gap-2 mb-3 flex-wrap">
    <div class="input-group" style="max-width:300px;">
        <input type="text" class="form-control" placeholder="Search household head…" style="font-size:13px;border-radius:8px 0 0 8px;">
        <button class="btn btn-primary" style="border-radius:0 8px 8px 0;padding:0 14px;"><i class="bi bi-search"></i></button>
    </div>
    <select class="form-select" style="width:auto;font-size:13px;border-radius:8px;">
        <option>All Puroks</option>
        @foreach($puroks as $p)<option>{{ $p[0] }}</option>@endforeach
    </select>
</div>
<div class="table-card">
    <div class="table-header">
        <span class="heading">Households <span class="badge bg-light text-secondary fw-600 ms-1" style="font-size:12px;">1,204</span></span>
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size:12.5px;border-radius:7px;">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Household Head</th>
                    <th>Purok</th>
                    <th>Address</th>
                    <th>Members</th>
                    <th>Voters</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $hh = [
                    ['Maria Santos',   'Purok 3','123 Mabini St.',    5, 3],
                    ['Juan dela Cruz', 'Purok 1','45 Rizal Ave.',     4, 2],
                    ['Ana Reyes',      'Purok 5','78 Gen. Luna St.',  6, 4],
                    ['Rosa Aquino',    'Purok 3','90 Mabini St.',     3, 2],
                    ['Ernesto Lim',    'Purok 6','12 Burgos St.',     7, 5],
                    ['Luz Villanueva', 'Purok 4','34 Bonifacio Ave.', 4, 3],
                ];
                @endphp
                @foreach($hh as $h)
                <tr>
                    <td style="font-weight:600;">{{ $h[0] }}</td>
                    <td style="font-size:13px;">{{ $h[1] }}</td>
                    <td style="font-size:13px;color:#64748b;">{{ $h[2] }}</td>
                    <td><span class="fw-700">{{ $h[3] }}</span></td>
                    <td>{{ $h[4] }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;"><i class="bi bi-eye" style="font-size:13px;"></i></button>
                            <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;"><i class="bi bi-pencil" style="font-size:13px;"></i></button>
                            <button class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;"><i class="bi bi-trash" style="font-size:13px;"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-top:1px solid #f1f5f9;font-size:13px;color:#64748b;">
        <span>Showing <strong>1–6</strong> of <strong>1,204</strong> households</span>
        <nav><ul class="pagination pagination-sm mb-0" style="gap:3px;">
            <li class="page-item disabled"><a class="page-link" style="border-radius:6px;">&laquo;</a></li>
            <li class="page-item active"><a class="page-link" style="border-radius:6px;">1</a></li>
            <li class="page-item"><a class="page-link" style="border-radius:6px;">2</a></li>
            <li class="page-item"><a class="page-link" style="border-radius:6px;">&raquo;</a></li>
        </ul></nav>
    </div>
</div>

@endsection

@section('scripts')
<script>
const purokLabels = ['Purok 1','Purok 2','Purok 3','Purok 4','Purok 5','Purok 6','Purok 7','Purok 8'];
new Chart(document.getElementById('voterPurokChart'), {
    type: 'bar',
    data: {
        labels: purokLabels,
        datasets: [
            { label: 'Registered Voters', data: [310,380,260,410,320,290,450,340], backgroundColor: '#1a56db', borderRadius: 5, borderSkipped: false },
            { label: 'Total 18+',         data: [380,450,320,490,380,345,530,410], backgroundColor: '#bfdbfe', borderRadius: 5, borderSkipped: false },
        ]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
        },
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, boxWidth: 10, padding: 12 } } }
    }
});
new Chart(document.getElementById('familySizeChart'), {
    type: 'bar',
    data: {
        labels: ['1','2','3','4','5','6','7+'],
        datasets: [{ label: 'Households', data: [48,142,198,310,280,148,78], backgroundColor: '#7c3aed', borderRadius: 5, borderSkipped: false }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>
@endsection