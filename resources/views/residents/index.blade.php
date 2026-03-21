{{--
    residents.blade.php
    Route: GET /residents  →  route('residents.index')
--}}
@extends('layouts.app')

@section('title', 'Residents – Barangay Management System')
@section('page-title', 'Resident Management')

@section('styles')
<style>
    .si-blue   { background: #eff6ff; color: #1a56db; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-red    { background: #fef2f2; color: #dc2626; }
    .si-amber  { background: #fffbeb; color: #d97706; }

    .badge-active   { background: #dcfce7; color: #15803d; }
    .badge-deceased { background: #fee2e2; color: #dc2626; }
    .badge-transfer { background: #fef3c7; color: #b45309; }

    .search-input-group .form-control { border-right: none; font-size: 13.5px; border-radius: 8px 0 0 8px; }
    .search-input-group .btn          { border-radius: 0 8px 8px 0; font-size: 13px; }

    .filter-chip {
        font-size: 12px; font-weight: 600;
        padding: 5px 14px; border-radius: 20px;
        border: 1px solid #e2e8f0; background: #fff; color: #64748b;
        cursor: pointer; transition: all .15s;
    }
    .filter-chip:hover, .filter-chip.active {
        background: #eff6ff; border-color: #93c5fd; color: #1a56db;
    }

    .res-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        font-size: 12px; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .ra-m { background: #dbeafe; color: #1d4ed8; }
    .ra-f { background: #fce7f3; color: #be185d; }

    .chart-wrap { position: relative; height: 220px; }
</style>
@endsection

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Resident Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Manage all registered residents of the barangay.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2"
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-person-plus-fill"></i> Add New Resident
    </button>
</div>

{{-- ── STAT WIDGETS ── --}}
<div class="section-heading">Overview</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-blue"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value">4,821</div>
                <div class="stat-label">Total Residents</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-green"><i class="bi bi-person-check-fill"></i></div>
            <div>
                <div class="stat-value">4,503</div>
                <div class="stat-label">Active Residents</div>
                <span class="stat-badge badge-active">93.4%</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-red"><i class="bi bi-person-dash-fill"></i></div>
            <div>
                <div class="stat-value">187</div>
                <div class="stat-label">Deceased</div>
                <span class="stat-badge badge-deceased">3.9%</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-amber"><i class="bi bi-person-walking"></i></div>
            <div>
                <div class="stat-value">131</div>
                <div class="stat-label">Transferred</div>
                <span class="stat-badge badge-transfer">2.7%</span>
            </div>
        </div>
    </div>
</div>

{{-- ── CHARTS ── --}}
<div class="section-heading">Demographics</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="chart-card h-100">
            <div class="card-heading">Gender Distribution</div>
            <div class="card-sub mb-3">Active residents only</div>
            <div class="chart-wrap"><canvas id="genderChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="chart-card h-100">
            <div class="card-heading">Age Group Breakdown</div>
            <div class="card-sub mb-3">All registered residents</div>
            <div class="chart-wrap"><canvas id="ageChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="chart-card h-100">
            <div class="card-heading">Voter Status</div>
            <div class="card-sub mb-3">18+ residents</div>
            <div class="chart-wrap"><canvas id="voterChart"></canvas></div>
            <div class="d-flex justify-content-center gap-3 mt-3">
                <div class="d-flex align-items-center gap-1" style="font-size:12px;">
                    <span style="width:10px;height:10px;border-radius:2px;background:#1a56db;display:inline-block;"></span> Registered
                </div>
                <div class="d-flex align-items-center gap-1" style="font-size:12px;">
                    <span style="width:10px;height:10px;border-radius:2px;background:#e2e8f0;display:inline-block;"></span> Not Registered
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── RESIDENT LIST ── --}}
<div class="section-heading">Resident List</div>
<div class="d-flex align-items-center flex-wrap gap-2 mb-3">
    <div class="input-group search-input-group" style="max-width:340px;">
        <input type="text" class="form-control" placeholder="Search name, age, address…">
        <button class="btn btn-primary" style="padding:0 14px;"><i class="bi bi-search"></i></button>
    </div>
    <select class="form-select" style="width:auto;font-size:13px;border-radius:8px;">
        <option value="">All Genders</option>
        <option>Male</option><option>Female</option><option>Other</option>
    </select>
    <select class="form-select" style="width:auto;font-size:13px;border-radius:8px;">
        <option value="">All Status</option>
        <option>Active</option><option>Deceased</option><option>Transferred</option>
    </select>
    <div class="d-flex gap-1 ms-auto flex-wrap">
        <button class="filter-chip active">All</button>
        <button class="filter-chip">Seniors</button>
        <button class="filter-chip">Youth</button>
        <button class="filter-chip">Voters</button>
        <button class="filter-chip">PWD</button>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <span class="heading">Residents <span class="badge bg-light text-secondary fw-600 ms-1" style="font-size:12px;">4,821</span></span>
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size:12.5px;border-radius:7px;">
            <i class="bi bi-download"></i> Export
        </button>
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size:12.5px;border-radius:7px;">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" class="form-check-input"></th>
                    <th>Resident</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Address / Purok</th>
                    <th>Voter</th>
                    <th>Status</th>
                    <th style="width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $rows = [
                    ['Maria Santos',   28, 'F', 'Purok 3 – Mabini St.',  true,  'active'],
                    ['Juan dela Cruz', 45, 'M', 'Purok 1 – Rizal Ave.',  true,  'active'],
                    ['Ana Reyes',      72, 'F', 'Purok 5 – Gen. Luna',   true,  'active'],
                    ['Pedro Bautista', 60, 'M', 'Purok 2 – Mabini St.',  false, 'deceased'],
                    ['Luz Villanueva', 34, 'F', 'Purok 4 – Bonifacio',   true,  'transfer'],
                    ['Carlo Mendoza',  19, 'M', 'Purok 1 – Aguinaldo',   false, 'active'],
                    ['Rosa Aquino',    55, 'F', 'Purok 3 – Mabini St.',  true,  'active'],
                    ['Ernesto Lim',    38, 'M', 'Purok 6 – Rizal Ave.',  true,  'active'],
                ];
                $statusMap = [
                    'active'   => ['badge-active',   'Active'],
                    'deceased' => ['badge-deceased', 'Deceased'],
                    'transfer' => ['badge-transfer', 'Transferred'],
                ];
                @endphp
                @foreach($rows as $r)
                @php
                    [$name, $age, $sex, $addr, $voter, $status] = array_values($r);
                    $initials   = collect(explode(' ', $name))->map(fn($w) => $w[0])->take(2)->implode('');
                    [$bc, $bl]  = $statusMap[$status];
                    $avatarClass = $sex === 'F' ? 'ra-f' : 'ra-m';
                @endphp
                <tr>
                    <td><input type="checkbox" class="form-check-input"></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="res-avatar {{ $avatarClass }}">{{ $initials }}</div>
                            <span style="font-weight:600;">{{ $name }}</span>
                        </div>
                    </td>
                    <td>{{ $age }}</td>
                    <td>{{ $sex === 'M' ? 'Male' : 'Female' }}</td>
                    <td style="font-size:13px;color:#64748b;">{{ $addr }}</td>
                    <td>
                        @if($voter)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-dash-circle text-secondary"></i>
                        @endif
                    </td>
                    <td><span class="status-badge {{ $bc }}">{{ $bl }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="View">
                                <i class="bi bi-eye" style="font-size:13px;"></i>
                            </button>
                            <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Edit">
                                <i class="bi bi-pencil" style="font-size:13px;"></i>
                            </button>
                            <button class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;" title="Delete">
                                <i class="bi bi-trash" style="font-size:13px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex align-items-center justify-content-between px-4 py-3"
         style="border-top:1px solid #f1f5f9;font-size:13px;color:#64748b;">
        <span>Showing <strong>1–8</strong> of <strong>4,821</strong> residents</span>
        <nav>
            <ul class="pagination pagination-sm mb-0" style="gap:3px;">
                <li class="page-item disabled"><a class="page-link" style="border-radius:6px;">&laquo;</a></li>
                <li class="page-item active"><a class="page-link" style="border-radius:6px;">1</a></li>
                <li class="page-item"><a class="page-link" style="border-radius:6px;">2</a></li>
                <li class="page-item"><a class="page-link" style="border-radius:6px;">3</a></li>
                <li class="page-item"><a class="page-link" style="border-radius:6px;">&raquo;</a></li>
            </ul>
        </nav>
    </div>
</div>

@endsection

@section('scripts')
<script>
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: ['Male', 'Female', 'Other'],
        datasets: [{ data: [2310, 2180, 13], backgroundColor: ['#1a56db','#f472b6','#94a3b8'], borderWidth: 0, hoverOffset: 6 }]
    },
    options: {
        cutout: '68%', maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 }, boxWidth: 10, padding: 14 } } }
    }
});

new Chart(document.getElementById('ageChart'), {
    type: 'bar',
    data: {
        labels: ['0–12','13–17','18–24','25–34','35–49','50–64','65+'],
        datasets: [{ label: 'Residents', data: [620,410,590,840,970,760,631], backgroundColor: '#1a56db', borderRadius: 6, borderSkipped: false }]
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

new Chart(document.getElementById('voterChart'), {
    type: 'doughnut',
    data: {
        labels: ['Registered','Not Registered'],
        datasets: [{ data: [2850, 820], backgroundColor: ['#1a56db','#e2e8f0'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { cutout: '72%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
</script>
@endsection