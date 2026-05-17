{{--
    officials.blade.php
    Route: GET /officials  →  route('officials.index')
--}}
@extends('layouts.app')

@section('title', 'Officials & Staff – Barangay Management System')
@section('page-title', 'Officials & Staff Management')

@section('styles')
<style>
    .si-blue   { background: #eff6ff; color: #1a56db; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-violet { background: #f5f3ff; color: #7c3aed; }
    .si-amber  { background: #fffbeb; color: #d97706; }

    /* Official profile card */
    .official-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 20px; text-align: center; transition: box-shadow .2s;
        position: relative; overflow: hidden;
    }
    .official-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); }
    .official-card .oc-stripe {
        position: absolute; top: 0; left: 0; right: 0; height: 4px;
    }
    .official-card .oc-avatar {
        width: 60px; height: 60px; border-radius: 50%;
        font-size: 20px; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center;
        margin-bottom: 10px;
    }
    .official-card .oc-name  { font-size: 13.5px; font-weight: 800; color: #0f172a; }
    .official-card .oc-pos   { font-size: 11.5px; color: #64748b; font-weight: 500; margin-top: 2px; }
    .official-card .oc-term  { font-size: 11px; color: #94a3b8; margin-top: 6px; }
    .official-card .oc-actions { margin-top: 12px; display: flex; gap: 6px; justify-content: center; }

    /* Digital ID preview */
    .digital-id {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
        border-radius: 14px; padding: 20px 22px; color: #fff;
        position: relative; overflow: hidden;
        min-height: 180px;
    }
    .digital-id::before {
        content: ''; position: absolute;
        width: 200px; height: 200px; border-radius: 50%;
        background: rgba(255,255,255,.04);
        top: -60px; right: -60px;
    }
    .digital-id .did-logo  { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #93c5fd; }
    .digital-id .did-name  { font-size: 18px; font-weight: 800; margin-top: 10px; }
    .digital-id .did-pos   { font-size: 12px; color: #93c5fd; margin-top: 2px; }
    .digital-id .did-id    { font-size: 11px; font-family: 'DM Mono',monospace; color: #7dd3fc; margin-top: 6px; }
    .digital-id .did-term  { font-size: 11px; color: #94a3b8; margin-top: 4px; }
    .digital-id .did-qr {
        position: absolute; bottom: 16px; right: 16px;
        width: 56px; height: 56px; border-radius: 8px;
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; color: rgba(255,255,255,.5);
    }

    .term-bar-wrap { height: 6px; background: #e2e8f0; border-radius: 3px; margin-top: 6px; overflow: hidden; }
    .term-bar { height: 100%; border-radius: 3px; background: #1a56db; }

    .chart-wrap { position: relative; height: 180px; }
</style>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Officials & Staff Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Manage barangay officials, staff profiles, and term tracking.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13px;font-weight:600;padding:8px 16px;">
            <i class="bi bi-person-badge-fill"></i> Assign Designation
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-person-plus-fill"></i> Add Official Profile
        </button>
    </div>
</div>

{{-- ── STAT WIDGETS ── --}}
<div class="section-heading">Overview</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-blue"><i class="bi bi-shield-fill-check"></i></div>
            <div><div class="stat-value">18</div><div class="stat-label">Total Officials & Staff</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-violet"><i class="bi bi-people-fill"></i></div>
            <div><div class="stat-value">8</div><div class="stat-label">Barangay Kagawads</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-green"><i class="bi bi-calendar-check-fill"></i></div>
            <div>
                <div class="stat-value">3 yrs</div>
                <div class="stat-label">Current Term Duration</div>
                <span class="stat-badge" style="background:#dcfce7;color:#15803d;">2022–2025</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-amber"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value">10 mos</div>
                <div class="stat-label">Until Term Ends</div>
                <div class="term-bar-wrap" style="width:100%;"><div class="term-bar" style="width:72%;"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- ── OFFICIAL CARDS ── --}}
<div class="section-heading">Barangay Officials</div>
<div class="row g-3 mb-4">
    @php
    $officials = [
        ['HON. Roberto M. Cruz',   'Punong Barangay',         'RC', '#1a56db', '#dbeafe', '2022–2025'],
        ['HON. Maria L. Santos',   'Kagawad – Peace & Order', 'MS', '#7c3aed', '#ede9fe', '2022–2025'],
        ['HON. Juan P. dela Cruz', 'Kagawad – Health',        'JC', '#0d9488', '#ccfbf1', '2022–2025'],
        ['HON. Ana B. Reyes',      'Kagawad – Education',     'AR', '#ea580c', '#ffedd5', '2022–2025'],
        ['HON. Rosa T. Aquino',    'Kagawad – Infrastructure','RA', '#16a34a', '#dcfce7', '2022–2025'],
        ['HON. Ernesto C. Lim',    'Kagawad – Environment',   'EL', '#db2777', '#fce7f3', '2022–2025'],
        ['HON. Carlo R. Mendoza',  'Kagawad – Livelihood',    'CM', '#d97706', '#fef3c7', '2022–2025'],
        ['HON. Luz A. Villanueva', 'Kagawad – BDRRM',         'LV', '#0369a1', '#e0f2fe', '2022–2025'],
        ['SK Chair Ana P. Flores', 'SK Chairperson',          'AF', '#9333ea', '#f3e8ff', '2023–2026'],
        ['Sec. Pedro B. Garcia',   'Barangay Secretary',      'PG', '#64748b', '#f1f5f9', 'Appointive'],
        ['Treas. Nena D. Torres',  'Barangay Treasurer',      'NT', '#64748b', '#f1f5f9', 'Appointive'],
    ];
    @endphp
    @foreach($officials as $o)
    <div class="col-6 col-md-4 col-xl-3">
        <div class="official-card">
            <div class="oc-stripe" style="background:{{ $o[3] }};"></div>
            <div class="oc-avatar mt-2" style="background:{{ $o[4] }};color:{{ $o[3] }};">{{ $o[2] }}</div>
            <div class="oc-name">{{ $o[0] }}</div>
            <div class="oc-pos">{{ $o[1] }}</div>
            <div class="oc-term"><i class="bi bi-calendar2 me-1"></i>{{ $o[5] }}</div>
            <div class="oc-actions">
                <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 10px;font-size:12px;">
                    <i class="bi bi-eye me-1"></i> View
                </button>
                <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 10px;font-size:12px;">
                    <i class="bi bi-credit-card-2-front me-1"></i> ID
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── DIGITAL ID PREVIEW + CHARTS ── --}}
<div class="row g-3">
    <div class="col-lg-4">
        <div class="section-heading">Digital ID Preview</div>
        <div class="digital-id">
            <div class="did-logo">Barangay Uno · Official ID</div>
            <div class="did-name">HON. ROBERTO M. CRUZ</div>
            <div class="did-pos">Punong Barangay</div>
            <div class="did-id">ID No.: BRG-OFF-2022-001</div>
            <div class="did-term">Term: 2022 – 2025</div>
            <div class="did-qr"><i class="bi bi-qr-code"></i></div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-outline-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2"
                    style="border-radius:8px;font-size:13px;font-weight:600;">
                <i class="bi bi-printer-fill"></i> Print ID
            </button>
            <button class="btn btn-outline-secondary d-flex align-items-center gap-1"
                    style="border-radius:8px;font-size:13px;padding:8px 14px;">
                <i class="bi bi-download"></i>
            </button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="section-heading">Designation Breakdown</div>
        <div class="chart-card h-auto">
            <div class="chart-wrap"><canvas id="designationChart"></canvas></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="section-heading">Term Duration Tracker</div>
        <div class="chart-card">
            @php
            $termItems = [
                ['Punong Barangay',    72, '#1a56db'],
                ['Kagawad (8)',        72, '#7c3aed'],
                ['SK Chairperson',     28, '#9333ea'],
                ['Secretary',         100, '#64748b'],
                ['Treasurer',         100, '#64748b'],
            ];
            @endphp
            @foreach($termItems as $t)
            <div class="mb-3">
                <div class="d-flex justify-content-between" style="font-size:12.5px;font-weight:600;color:#0f172a;">
                    <span>{{ $t[0] }}</span>
                    <span style="color:{{ $t[2] }};">{{ $t[1] }}%</span>
                </div>
                <div class="term-bar-wrap"><div class="term-bar" style="width:{{ $t[1] }}%;background:{{ $t[2] }};"></div></div>
            </div>
            @endforeach
            <div style="font-size:11px;color:#94a3b8;margin-top:8px;">Term completion as of February 2025</div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
new Chart(document.getElementById('designationChart'), {
    type: 'doughnut',
    data: {
        labels: ['Punong Barangay','Kagawads','SK Chair','Secretary','Treasurer','Other Staff'],
        datasets: [{ data: [1,8,1,1,1,6], backgroundColor: ['#1a56db','#7c3aed','#9333ea','#64748b','#64748b','#cbd5e1'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { cutout: '60%', maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, boxWidth: 10, padding: 10 } } }
    }
});
</script>
@endsection