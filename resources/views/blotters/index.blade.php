{{--
    blotter.blade.php
    Route: GET /blotter  →  route('blotter.index')
--}}
@extends('layouts.app')

@section('title', 'Blotter Management – Barangay Management System')
@section('page-title', 'Blotter Management')

@section('styles')
<style>
    .si-red    { background: #fef2f2; color: #dc2626; }
    .si-amber  { background: #fffbeb; color: #d97706; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-blue   { background: #eff6ff; color: #1a56db; }

    .badge-open       { background: #fee2e2; color: #dc2626; }
    .badge-ongoing    { background: #fef3c7; color: #b45309; }
    .badge-resolved   { background: #dcfce7; color: #15803d; }
    .badge-dismissed  { background: #f1f5f9; color: #64748b; }

    .severity-high   { color: #dc2626; font-weight: 700; }
    .severity-medium { color: #d97706; font-weight: 700; }
    .severity-low    { color: #16a34a; font-weight: 700; }

    .timeline { position: relative; padding-left: 28px; }
    .timeline::before {
        content: ''; position: absolute;
        left: 8px; top: 0; bottom: 0;
        width: 2px; background: #e2e8f0;
    }
    .timeline-item { position: relative; margin-bottom: 20px; }
    .timeline-item::before {
        content: ''; position: absolute;
        left: -24px; top: 5px;
        width: 10px; height: 10px;
        border-radius: 50%; background: #1a56db;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #1a56db;
    }
    .timeline-item.resolved::before { background: #16a34a; box-shadow: 0 0 0 2px #16a34a; }
    .timeline-item.ongoing::before  { background: #d97706; box-shadow: 0 0 0 2px #d97706; }
    .timeline-date  { font-size: 11px; color: #94a3b8; font-weight: 600; margin-bottom: 2px; }
    .timeline-title { font-size: 13.5px; font-weight: 700; color: #0f172a; }
    .timeline-sub   { font-size: 12px; color: #64748b; margin-top: 2px; }

    .chart-wrap { position: relative; height: 200px; }

    .attach-zone {
        border: 2px dashed #cbd5e1; border-radius: 10px;
        padding: 24px; text-align: center;
        color: #94a3b8; font-size: 13px; cursor: pointer;
        transition: all .2s;
    }
    .attach-zone:hover { border-color: #1a56db; background: #eff6ff; color: #1a56db; }
</style>
@endsection

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Blotter Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Log, track, and resolve barangay complaints and incidents.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2"
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-plus-lg"></i> Add New Blotter Record
    </button>
</div>

{{-- ── STAT WIDGETS ── --}}
<div class="section-heading">Overview</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-blue"><i class="bi bi-journal-text"></i></div>
            <div>
                <div class="stat-value">342</div>
                <div class="stat-label">Total Complaints Logged</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-red"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div>
                <div class="stat-value">48</div>
                <div class="stat-label">Open Cases</div>
                <span class="stat-badge badge-open">Needs action</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-amber"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value">21</div>
                <div class="stat-label">Ongoing / Mediation</div>
                <span class="stat-badge badge-ongoing">In progress</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-green"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="stat-value">273</div>
                <div class="stat-label">Resolved Cases</div>
                <span class="stat-badge badge-resolved">79.8%</span>
            </div>
        </div>
    </div>
</div>

{{-- ── CASE STATUS CHART + ATTACH DOCS ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="chart-card h-100">
            <div class="card-heading">Case Status Distribution</div>
            <div class="card-sub mb-3">All logged complaints</div>
            <div class="chart-wrap"><canvas id="caseStatusChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card h-100">
            <div class="card-heading">Complaint Type Breakdown</div>
            <div class="card-sub mb-3">This year</div>
            <div class="chart-wrap"><canvas id="complaintTypeChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card h-100 d-flex flex-column">
            <div class="card-heading">Attach Supporting Documents</div>
            <div class="card-sub mb-3">Upload files for an existing blotter record</div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Blotter Reference No.</label>
                <input type="text" class="form-control" placeholder="e.g. BLT-2025-0048" style="font-size:13.5px;border-radius:8px;">
            </div>
            <div class="attach-zone flex-grow-1 d-flex flex-column align-items-center justify-content-center gap-2">
                <i class="bi bi-cloud-arrow-up-fill" style="font-size:28px;"></i>
                <div style="font-weight:600;">Drop files here or click to browse</div>
                <div style="font-size:11px;">PDF, JPG, PNG — max 10MB each</div>
            </div>
            <button class="btn btn-outline-primary mt-3 w-100" style="border-radius:8px;font-size:13px;font-weight:600;">
                <i class="bi bi-paperclip me-1"></i> Attach to Record
            </button>
        </div>
    </div>
</div>

{{-- ── RECENT INCIDENTS TABLE ── --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="section-heading">Recent Incidents</div>
        <div class="table-card">
            <div class="table-header">
                <span class="heading">Incident Log</span>
                <div class="input-group" style="max-width:220px;">
                    <input type="text" class="form-control" placeholder="Search…" style="font-size:12.5px;border-radius:8px 0 0 8px;">
                    <button class="btn btn-outline-secondary" style="border-radius:0 8px 8px 0;padding:0 10px;"><i class="bi bi-search" style="font-size:12px;"></i></button>
                </div>
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size:12.5px;border-radius:7px;">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ref. No.</th>
                            <th>Complainant</th>
                            <th>Incident Type</th>
                            <th>Date Filed</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $incidents = [
                            ['BLT-2025-0048','Maria Santos',   'Noise Complaint',     'Feb 24',  'low',    'open'],
                            ['BLT-2025-0047','Juan dela Cruz', 'Physical Altercation','Feb 23',  'high',   'ongoing'],
                            ['BLT-2025-0046','Rosa Aquino',    'Property Damage',     'Feb 22',  'medium', 'open'],
                            ['BLT-2025-0045','Carlo Mendoza',  'Theft',               'Feb 20',  'high',   'ongoing'],
                            ['BLT-2025-0044','Ana Reyes',      'Verbal Abuse',        'Feb 19',  'medium', 'resolved'],
                            ['BLT-2025-0043','Ernesto Lim',    'Trespassing',         'Feb 17',  'low',    'resolved'],
                            ['BLT-2025-0042','Luz Villanueva', 'Domestic Dispute',    'Feb 15',  'high',   'resolved'],
                        ];
                        $statusMap = ['open'=>['badge-open','Open'],'ongoing'=>['badge-ongoing','Ongoing'],'resolved'=>['badge-resolved','Resolved'],'dismissed'=>['badge-dismissed','Dismissed']];
                        @endphp
                        @foreach($incidents as $inc)
                        @php [$ref,$name,$type,$date,$sev,$status] = $inc; [$bc,$bl] = $statusMap[$status]; @endphp
                        <tr>
                            <td style="font-family:'DM Mono',monospace;font-size:11.5px;color:#64748b;">{{ $ref }}</td>
                            <td style="font-weight:600;font-size:13px;">{{ $name }}</td>
                            <td style="font-size:13px;">{{ $type }}</td>
                            <td style="font-size:12px;color:#64748b;">{{ $date }}</td>
                            <td><span class="severity-{{ $sev }}">{{ ucfirst($sev) }}</span></td>
                            <td><span class="status-badge {{ $bc }}">{{ $bl }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="View">
                                    <i class="bi bi-eye" style="font-size:13px;"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex align-items-center justify-content-between px-4 py-3"
                 style="border-top:1px solid #f1f5f9;font-size:13px;color:#64748b;">
                <span>Showing <strong>1–7</strong> of <strong>342</strong> records</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0" style="gap:3px;">
                        <li class="page-item disabled"><a class="page-link" style="border-radius:6px;">&laquo;</a></li>
                        <li class="page-item active"><a class="page-link" style="border-radius:6px;">1</a></li>
                        <li class="page-item"><a class="page-link" style="border-radius:6px;">2</a></li>
                        <li class="page-item"><a class="page-link" style="border-radius:6px;">&raquo;</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    {{-- Case Status Tracker --}}
    <div class="col-lg-5">
        <div class="section-heading">Case Status Tracker</div>
        <div class="chart-card">
            <div class="timeline">
                @php
                $events = [
                    ['resolved','Feb 24','BLT-2025-0044 Closed','Verbal abuse case — settled via mediation.'],
                    ['ongoing', 'Feb 23','BLT-2025-0047 Mediation Set','Physical altercation — hearing scheduled Mar 1.'],
                    ['open',    'Feb 22','BLT-2025-0046 Filed','Property damage complaint received.'],
                    ['resolved','Feb 20','BLT-2025-0040 Closed','Noise ordinance — amicably settled.'],
                    ['ongoing', 'Feb 19','BLT-2025-0045 Follow-up','Theft case referred to PNP.'],
                    ['resolved','Feb 17','BLT-2025-0038 Closed','Trespassing — warning issued, closed.'],
                ];
                @endphp
                @foreach($events as $e)
                <div class="timeline-item {{ $e[0] }}">
                    <div class="timeline-date">{{ $e[1] }}</div>
                    <div class="timeline-title">{{ $e[2] }}</div>
                    <div class="timeline-sub">{{ $e[3] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
new Chart(document.getElementById('caseStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Open','Ongoing','Resolved','Dismissed'],
        datasets: [{ data: [48,21,273,0], backgroundColor: ['#dc2626','#f59e0b','#16a34a','#94a3b8'], borderWidth: 0, hoverOffset: 5 }]
    },
    options: { cutout: '65%', maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, boxWidth: 10, padding: 12 } } }
    }
});
new Chart(document.getElementById('complaintTypeChart'), {
    type: 'bar',
    data: {
        labels: ['Noise','Altercation','Theft','Damage','Domestic','Trespass','Other'],
        datasets: [{ data: [72,58,45,38,61,24,44], backgroundColor: '#1a56db', borderRadius: 5, borderSkipped: false }]
    },
    options: { maintainAspectRatio: false, indexAxis: 'y',
        scales: { x: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } },
        plugins: { legend: { display: false } }
    }
});
</script>
@endsection