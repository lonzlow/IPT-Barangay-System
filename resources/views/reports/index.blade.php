{{--
    reports.blade.php
    Route: GET /reports  →  route('reports.index')
--}}
@extends('layouts.app')

@section('title', 'Reports & Analytics – Barangay Management System')
@section('page-title', 'Reports & Analytics')

@section('styles')
<style>
    .si-blue   { background: #eff6ff; color: #1a56db; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-violet { background: #f5f3ff; color: #7c3aed; }
    .si-amber  { background: #fffbeb; color: #d97706; }

    .chart-wrap    { position: relative; height: 220px; }
    .chart-wrap-lg { position: relative; height: 280px; }

    .report-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 0; border-bottom: 1px solid #f1f5f9;
    }
    .report-item:last-child { border-bottom: none; }
    .report-item .ri-icon {
        width: 38px; height: 38px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .report-item .ri-name { font-size: 13.5px; font-weight: 700; color: #0f172a; }
    .report-item .ri-meta { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }
    .report-item .ri-badge{
        font-size: 11px; font-weight: 600; padding: 2px 9px; border-radius: 20px;
        margin-left: auto; flex-shrink: 0;
    }

    .export-btn {
        background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px;
        padding: 14px 18px; display: flex; align-items: center; gap: 12px;
        cursor: pointer; transition: all .18s; text-decoration: none; color: inherit;
    }
    .export-btn:hover { border-color: #1a56db; background: #eff6ff; color: inherit; }
    .export-btn .eb-icon {
        width: 38px; height: 38px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;
    }
    .export-btn .eb-label { font-size: 13px; font-weight: 700; color: #0f172a; }
    .export-btn .eb-sub   { font-size: 11px; color: #94a3b8; }
</style>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Reports & Analytics</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Population statistics, demographic breakdowns, and exportable reports.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <select class="form-select" style="width:auto;font-size:13px;border-radius:8px;font-weight:600;">
            <option>Monthly Report</option>
            <option>Quarterly Report</option>
            <option>Annual Report</option>
        </select>
        <select class="form-select" style="width:auto;font-size:13px;border-radius:8px;">
            <option>2025</option><option>2024</option><option>2023</option>
        </select>
        <button class="btn btn-primary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-file-earmark-bar-graph-fill"></i> Generate Report
        </button>
    </div>
</div>

{{-- ── SUMMARY WIDGETS ── --}}
<div class="section-heading">Summary</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-blue"><i class="bi bi-people-fill"></i></div>
            <div><div class="stat-value">4,821</div><div class="stat-label">Total Population</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-green"><i class="bi bi-check2-circle"></i></div>
            <div><div class="stat-value">2,850</div><div class="stat-label">Registered Voters</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-violet"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div><div class="stat-value">38</div><div class="stat-label">Reports Generated (YTD)</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-amber"><i class="bi bi-calendar-month-fill"></i></div>
            <div>
                <div class="stat-value">Feb 2025</div>
                <div class="stat-label">Last Report Generated</div>
            </div>
        </div>
    </div>
</div>

{{-- ── POPULATION CHART + GENDER/AGE ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="card-heading">Population Growth (5-Year Trend)</div>
            <div class="card-sub mb-3">Total registered residents per year</div>
            <div class="chart-wrap-lg"><canvas id="populationChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="chart-card h-100">
            <div class="card-heading">Gender Breakdown</div>
            <div class="card-sub mb-3">Current population</div>
            <div class="chart-wrap"><canvas id="genderChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="chart-card h-100">
            <div class="card-heading">Voter Turnout</div>
            <div class="card-sub mb-3">18+ eligible voters</div>
            <div class="chart-wrap"><canvas id="voterChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="card-heading">Age Group Distribution</div>
            <div class="card-sub mb-3">All registered residents</div>
            <div class="chart-wrap"><canvas id="ageChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="card-heading">Document Issuance (Monthly)</div>
            <div class="card-sub mb-3">Total certificates issued per month</div>
            <div class="chart-wrap"><canvas id="docsChart"></canvas></div>
        </div>
    </div>
</div>

{{-- ── EXPORT + RECENT REPORTS ── --}}
<div class="row g-3">
    <div class="col-lg-5">
        <div class="section-heading">Export Reports</div>
        <div class="row g-2">
            @php
            $exports = [
                ['Population Report',       'PDF',   'bi-file-earmark-pdf-fill',   '#dc2626','#fef2f2'],
                ['Population Report',       'Excel', 'bi-file-earmark-excel-fill', '#16a34a','#f0fdf4'],
                ['Voter Statistics',        'PDF',   'bi-file-earmark-pdf-fill',   '#dc2626','#fef2f2'],
                ['Document Issuance Log',   'Excel', 'bi-file-earmark-excel-fill', '#16a34a','#f0fdf4'],
                ['Blotter Summary',         'PDF',   'bi-file-earmark-pdf-fill',   '#dc2626','#fef2f2'],
                ['Business Permit Report',  'Excel', 'bi-file-earmark-excel-fill', '#16a34a','#f0fdf4'],
            ];
            @endphp
            @foreach($exports as $e)
            <div class="col-6">
                <a href="#" class="export-btn">
                    <div class="eb-icon" style="background:{{ $e[4] }};color:{{ $e[3] }};"><i class="bi {{ $e[2] }}"></i></div>
                    <div>
                        <div class="eb-label">{{ $e[0] }}</div>
                        <div class="eb-sub">Export as {{ $e[1] }}</div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-7">
        <div class="section-heading">Recent Reports</div>
        <div class="chart-card">
            @php
            $reports = [
                ['February 2025 Monthly Report', 'Generated Feb 24', 'bi-file-earmark-pdf-fill',   '#dc2626','#fef2f2','PDF'],
                ['Q4 2024 Quarterly Report',      'Generated Jan 15', 'bi-file-earmark-pdf-fill',   '#dc2626','#fef2f2','PDF'],
                ['2024 Annual Population Report', 'Generated Jan 5',  'bi-file-earmark-excel-fill', '#16a34a','#f0fdf4','Excel'],
                ['January 2025 Monthly Report',   'Generated Jan 31', 'bi-file-earmark-pdf-fill',   '#dc2626','#fef2f2','PDF'],
                ['Document Issuance YTD 2024',    'Generated Dec 28', 'bi-file-earmark-excel-fill', '#16a34a','#f0fdf4','Excel'],
                ['Blotter Summary Q3 2024',       'Generated Oct 3',  'bi-file-earmark-pdf-fill',   '#dc2626','#fef2f2','PDF'],
            ];
            @endphp
            @foreach($reports as $r)
            <div class="report-item">
                <div class="ri-icon" style="background:{{ $r[4] }};color:{{ $r[3] }};"><i class="bi {{ $r[2] }}"></i></div>
                <div>
                    <div class="ri-name">{{ $r[0] }}</div>
                    <div class="ri-meta">{{ $r[1] }}</div>
                </div>
                <span class="ri-badge" style="background:{{ $r[4] }};color:{{ $r[3] }};">{{ $r[5] }}</span>
                <button class="btn btn-sm btn-light ms-2" style="border-radius:6px;padding:4px 10px;font-size:12px;">
                    <i class="bi bi-download me-1"></i> Download
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
new Chart(document.getElementById('populationChart'), {
    type: 'line',
    data: {
        labels: ['2021','2022','2023','2024','2025'],
        datasets: [{
            label: 'Population', data: [4310,4480,4620,4740,4821],
            borderColor: '#1a56db', backgroundColor: 'rgba(26,86,219,.08)',
            tension: .4, fill: true, pointBackgroundColor: '#1a56db', pointRadius: 5,
        }]
    },
    options: { maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } }, min: 4000 }
        },
        plugins: { legend: { display: false } }
    }
});
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: ['Male','Female','Other'],
        datasets: [{ data: [2310,2498,13], backgroundColor: ['#1a56db','#f472b6','#94a3b8'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { cutout: '65%', maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 10 }, boxWidth: 8, padding: 10 } } }
    }
});
new Chart(document.getElementById('voterChart'), {
    type: 'doughnut',
    data: {
        labels: ['Registered','Not Registered'],
        datasets: [{ data: [2850,820], backgroundColor: ['#16a34a','#e2e8f0'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { cutout: '65%', maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 10 }, boxWidth: 8, padding: 10 } } }
    }
});
new Chart(document.getElementById('ageChart'), {
    type: 'bar',
    data: {
        labels: ['0–4','5–12','13–17','18–24','25–34','35–49','50–64','65+'],
        datasets: [{
            label: 'Male',   data: [182,310,198,295,418,480,372,313], backgroundColor: '#1a56db', borderRadius: 4, borderSkipped: false
        },{
            label: 'Female', data: [175,298,212,302,430,492,390,318], backgroundColor: '#f472b6', borderRadius: 4, borderSkipped: false
        }]
    },
    options: { maintainAspectRatio: false,
        scales: {
            x: { stacked: false, grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { stacked: false, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
        },
        plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 11 }, boxWidth: 10, padding: 12 } } }
    }
});
new Chart(document.getElementById('docsChart'), {
    type: 'line',
    data: {
        labels: ['Sep','Oct','Nov','Dec','Jan','Feb'],
        datasets: [{
            label: 'Documents', data: [420,380,510,290,480,549],
            borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,.08)',
            tension: .4, fill: true, pointBackgroundColor: '#7c3aed', pointRadius: 4,
        }]
    },
    options: { maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>
@endsection