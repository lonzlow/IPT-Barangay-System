{{--
    business.blade.php
    Route: GET /business  →  route('business.index')
--}}
@extends('layouts.app')

@section('title', 'Business Permits – Barangay Management System')
@section('page-title', 'Business Permit Management')

@section('styles')
<style>
    .si-blue   { background: #eff6ff; color: #1a56db; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-amber  { background: #fffbeb; color: #d97706; }
    .si-red    { background: #fef2f2; color: #dc2626; }

    .badge-active   { background: #dcfce7; color: #15803d; }
    .badge-expiring { background: #fef3c7; color: #b45309; }
    .badge-expired  { background: #fee2e2; color: #dc2626; }
    .badge-pending  { background: #eff6ff; color: #1d4ed8; }

    .permit-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 20px; transition: box-shadow .2s;
    }
    .permit-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.07); }
    .permit-card .biz-name  { font-size: 14px; font-weight: 800; color: #0f172a; }
    .permit-card .biz-type  { font-size: 12px; color: #64748b; margin-top: 2px; }
    .permit-card .biz-meta  { font-size: 11.5px; color: #94a3b8; margin-top: 8px; }

    /* QR placeholder */
    .qr-placeholder {
        width: 100px; height: 100px;
        background: #f8fafc; border: 2px dashed #cbd5e1;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-direction: column; gap: 4px;
        font-size: 11px; color: #94a3b8; font-weight: 600;
    }

    .chart-wrap { position: relative; height: 200px; }

    .alert-row {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; border-radius: 10px;
        background: #fffbeb; border: 1px solid #fde68a;
        margin-bottom: 8px;
    }
    .alert-row .ar-icon { color: #d97706; font-size: 18px; flex-shrink: 0; }
    .alert-row .ar-biz  { font-size: 13px; font-weight: 700; color: #0f172a; }
    .alert-row .ar-sub  { font-size: 11.5px; color: #92400e; }
    .alert-row .ar-days { font-size: 12px; font-weight: 800; color: #d97706; margin-left: auto; white-space: nowrap; }
</style>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Business Permit Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Register, track, and renew barangay business clearances and permits.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2"
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-plus-lg"></i> Register New Business
    </button>
</div>

{{-- ── STAT WIDGETS ── --}}
<div class="section-heading">Overview</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-blue"><i class="bi bi-shop-window"></i></div>
            <div><div class="stat-value">318</div><div class="stat-label">Registered Businesses</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-green"><i class="bi bi-patch-check-fill"></i></div>
            <div><div class="stat-value">271</div><div class="stat-label">Active Permits</div><span class="stat-badge badge-active">85.2%</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-amber"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div><div class="stat-value">27</div><div class="stat-label">Expiring in 30 days</div><span class="stat-badge badge-expiring">Alert</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-red"><i class="bi bi-x-circle-fill"></i></div>
            <div><div class="stat-value">20</div><div class="stat-label">Expired / Lapsed</div><span class="stat-badge badge-expired">Renewal needed</span></div>
        </div>
    </div>
</div>

{{-- ── CHARTS + EXPIRING ALERT ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="chart-card h-100">
            <div class="card-heading">Permit Status Breakdown</div>
            <div class="card-sub mb-3">All registered businesses</div>
            <div class="chart-wrap"><canvas id="permitStatusChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card h-100">
            <div class="card-heading">Business Type Distribution</div>
            <div class="card-sub mb-3">By category</div>
            <div class="chart-wrap"><canvas id="bizTypeChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="chart-card h-100">
            <div class="card-heading" style="color:#b45309;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Expiring Soon
            </div>
            <div class="card-sub mb-3">Permits expiring within 30 days</div>
            @php
            $expiring = [
                ['Sari-sari ni Nena','Retail','7 days'],
                ['Tindahan ni Romy', 'Food Stall','12 days'],
                ['Kapehan Flores',   'Food Service','18 days'],
                ['Aling Rosa Dress', 'Tailoring','21 days'],
                ['JMC Welding Shop', 'Workshop','29 days'],
            ];
            @endphp
            @foreach($expiring as $e)
            <div class="alert-row">
                <i class="bi bi-shop ar-icon"></i>
                <div>
                    <div class="ar-biz">{{ $e[0] }}</div>
                    <div class="ar-sub">{{ $e[1] }}</div>
                </div>
                <div class="ar-days">{{ $e[2] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── ISSUE PERMIT + RENEWAL TRACKER ── --}}
<div class="row g-3">

    {{-- Issue Permit --}}
    <div class="col-lg-4">
        <div class="section-heading">Issue Permit</div>
        <div class="chart-card">
            <div class="card-heading mb-1"><i class="bi bi-qr-code me-2 text-primary"></i>Issue Business Clearance</div>
            <div class="card-sub mb-3">Generate a clearance with QR code / reference number</div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Business Name</label>
                <input type="text" class="form-control" placeholder="Search registered business…" style="font-size:13px;border-radius:8px;">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Permit Year</label>
                <select class="form-select" style="font-size:13px;border-radius:8px;">
                    <option>2025</option><option>2024</option>
                </select>
            </div>
            <div class="mb-4 d-flex align-items-center gap-3">
                <div class="qr-placeholder">
                    <i class="bi bi-qr-code" style="font-size:32px;color:#cbd5e1;"></i>
                    <span>QR Preview</span>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">Reference No.</div>
                    <div style="font-size:15px;font-weight:800;font-family:'DM Mono',monospace;color:#0f172a;">BIZ-2025-????</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Generated upon issuance</div>
                </div>
            </div>
            <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                    style="border-radius:8px;font-size:13.5px;font-weight:600;padding:10px;">
                <i class="bi bi-file-earmark-check-fill"></i> Issue Clearance
            </button>
        </div>
    </div>

    {{-- Renewal Tracker --}}
    <div class="col-lg-8">
        <div class="section-heading">Renewal Tracker</div>
        <div class="table-card">
            <div class="table-header">
                <span class="heading">Business Permits</span>
                <div class="input-group" style="max-width:220px;">
                    <input type="text" class="form-control" placeholder="Search…" style="font-size:12.5px;border-radius:8px 0 0 8px;">
                    <button class="btn btn-outline-secondary" style="border-radius:0 8px 8px 0;padding:0 10px;"><i class="bi bi-search" style="font-size:12px;"></i></button>
                </div>
                <select class="form-select" style="width:auto;font-size:12.5px;border-radius:8px;">
                    <option>All Status</option><option>Active</option><option>Expiring</option><option>Expired</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Permit No.</th>
                            <th>Business Name</th>
                            <th>Owner</th>
                            <th>Type</th>
                            <th>Issued</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $permits = [
                            ['BIZ-2025-0271','Sari-sari ni Nena','Nena Torres',   'Retail',       'Jan 5, 2025', 'Mar 4, 2025', 'expiring'],
                            ['BIZ-2025-0270','Kapehan Flores',   'Divina Flores', 'Food Service', 'Jan 4, 2025', 'Mar 15, 2025','expiring'],
                            ['BIZ-2025-0268','Santos Pharmacy',  'Maria Santos',  'Health',       'Jan 2, 2025', 'Dec 31, 2025','active'],
                            ['BIZ-2025-0265','Cruz Grocery',     'Juan Cruz',     'Retail',       'Jan 2, 2025', 'Dec 31, 2025','active'],
                            ['BIZ-2025-0240','Aling Rosa Dress', 'Rosa Aquino',   'Tailoring',    'Dec 1, 2024', 'Mar 22, 2025','expiring'],
                            ['BIZ-2024-0198','Lim Auto Supply',  'Ernesto Lim',   'Auto Parts',   'Mar 1, 2024', 'Feb 28, 2025','expired'],
                        ];
                        $bm = ['active'=>['badge-active','Active'],'expiring'=>['badge-expiring','Expiring'],'expired'=>['badge-expired','Expired'],'pending'=>['badge-pending','Pending']];
                        @endphp
                        @foreach($permits as $p)
                        @php [$pno,$bname,$owner,$type,$issued,$expires,$status] = $p; [$bc,$bl] = $bm[$status]; @endphp
                        <tr>
                            <td style="font-family:'DM Mono',monospace;font-size:11.5px;color:#64748b;">{{ $pno }}</td>
                            <td style="font-weight:700;font-size:13px;">{{ $bname }}</td>
                            <td style="font-size:12.5px;">{{ $owner }}</td>
                            <td style="font-size:12.5px;color:#64748b;">{{ $type }}</td>
                            <td style="font-size:12px;color:#64748b;">{{ $issued }}</td>
                            <td style="font-size:12px;color:#64748b;">{{ $expires }}</td>
                            <td><span class="status-badge {{ $bc }}">{{ $bl }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Renew">
                                    <i class="bi bi-arrow-repeat" style="font-size:13px;"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-top:1px solid #f1f5f9;font-size:13px;color:#64748b;">
                <span>Showing <strong>1–6</strong> of <strong>318</strong> permits</span>
                <nav><ul class="pagination pagination-sm mb-0" style="gap:3px;">
                    <li class="page-item disabled"><a class="page-link" style="border-radius:6px;">&laquo;</a></li>
                    <li class="page-item active"><a class="page-link" style="border-radius:6px;">1</a></li>
                    <li class="page-item"><a class="page-link" style="border-radius:6px;">2</a></li>
                    <li class="page-item"><a class="page-link" style="border-radius:6px;">&raquo;</a></li>
                </ul></nav>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
new Chart(document.getElementById('permitStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Active','Expiring Soon','Expired','Pending'],
        datasets: [{ data: [271,27,20,0], backgroundColor: ['#16a34a','#f59e0b','#dc2626','#94a3b8'], borderWidth: 0, hoverOffset: 5 }]
    },
    options: { cutout: '65%', maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, boxWidth: 10, padding: 12 } } }
    }
});
new Chart(document.getElementById('bizTypeChart'), {
    type: 'bar',
    data: {
        labels: ['Retail','Food','Services','Health','Workshop','Transport','Other'],
        datasets: [{ data: [98,72,55,28,34,18,13], backgroundColor: '#1a56db', borderRadius: 5, borderSkipped: false }]
    },
    options: { maintainAspectRatio: false, indexAxis: 'y',
        scales: {
            x: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
            y: { grid: { display: false }, ticks: { font: { size: 11 } } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>
@endsection