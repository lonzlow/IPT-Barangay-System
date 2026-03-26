{{--
    users.blade.php
    Route: GET /users  →  route('users.index')
--}}
@extends('layouts.app')

@section('title', 'User Management – Barangay Management System')
@section('page-title', 'User Management & Access Control')

@section('styles')
<style>
    .si-blue   { background: #eff6ff; color: #1a56db; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-violet { background: #f5f3ff; color: #7c3aed; }
    .si-amber  { background: #fffbeb; color: #d97706; }
    .si-red    { background: #fef2f2; color: #dc2626; }

    .badge-admin     { background: #ede9fe; color: #6d28d9; }
    .badge-secretary { background: #eff6ff; color: #1d4ed8; }
    .badge-committee { background: #f0fdf4; color: #15803d; }
    .badge-viewer    { background: #f1f5f9; color: #475569; }

    .badge-online  { background: #dcfce7; color: #15803d; }
    .badge-offline { background: #f1f5f9; color: #64748b; }

    .user-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        font-size: 12px; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .chart-wrap { position: relative; height: 200px; }

    /* Activity log */
    .log-item {
        display: flex; gap: 12px;
        padding: 10px 0; border-bottom: 1px solid #f1f5f9; align-items: flex-start;
    }
    .log-item:last-child { border-bottom: none; }
    .log-item .li-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0; margin-top: 1px;
    }
    .log-item .li-action { font-size: 13px; font-weight: 600; color: #0f172a; }
    .log-item .li-user   { font-size: 12px; color: #64748b; }
    .log-item .li-time   { font-size: 11px; color: #94a3b8; margin-left: auto; white-space: nowrap; }

    /* Backup status */
    .backup-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px;
    }
    .backup-indicator {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        display: inline-block;
    }
    .bi-success { background: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.15); }
    .bi-warning { background: #d97706; box-shadow: 0 0 0 3px rgba(217,119,6,.15); }
    .bi-fail    { background: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.15); }
</style>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">User Management & Access Control</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Manage system users, roles, permissions, and activity logs.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary d-flex align-items-center gap-2"
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-person-plus-fill"></i> Add New User
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- ── STAT WIDGETS ── --}}
<div class="section-heading">Overview</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-blue"><i class="bi bi-people-fill"></i></div>
            <div><div class="stat-value">{{ $total_users }}</div>
            <div class="stat-label">Total System Users</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-violet"><i class="bi bi-shield-lock-fill"></i></div>
            <div><div class="stat-value">{{ $total_admins }}</div>
            <div class="stat-label">Administrators</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-green"><i class="bi bi-person-check-fill"></i></div>
            <div><div class="stat-value">{{ $active_users }}</div>
            <div class="stat-label">Active Right Now</div><span class="stat-badge badge-online">Online</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon si-amber"><i class="bi bi-activity"></i></div>
            <div><div class="stat-value">{{ $total_actions_today }}</div>
            <div class="stat-label">Actions Today</div></div>
        </div>
    </div>
</div>

{{-- ── ROLE CHART + USERS TABLE ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="chart-card h-100">
            <div class="card-heading">Roles Distribution</div>
            <div class="card-sub mb-3">All system users</div>
            <div class="chart-wrap"><canvas id="rolesChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="table-card h-100">
            <div class="table-header">
                <span class="heading">System Users</span>
                <div class="input-group" style="max-width:220px;">
                    <input type="text" class="form-control" placeholder="Search user…" style="font-size:12.5px;border-radius:8px 0 0 8px;">
                    <button class="btn btn-outline-secondary" style="border-radius:0 8px 8px 0;padding:0 10px;"><i class="bi bi-search" style="font-size:12px;"></i></button>
                </div>
                <select class="form-select" style="width:auto;font-size:12.5px;border-radius:8px;">
                    <option>All Roles</option><option>Admin</option><option>Secretary</option><option>Committee</option><option>Viewer</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="display" id="users-table"> {{-- table table-hover mb-0 --}}
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>M.I.</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── ACTIVITY LOGS + BACKUP STATUS ── --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="section-heading">Activity Logs</div>
        <div class="chart-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="card-heading">Recent System Actions</div>
                    <div class="card-sub">All user activity today</div>
                </div>
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size:12px;border-radius:7px;">
                    <i class="bi bi-download"></i> Export Log
                </button>
            </div>
            @php
            $logs = [
                ['Issued Barangay Clearance',   'pedro.garcia · Document Issuance', 'Just now',    'bi-file-earmark-check-fill','#eff6ff','#1a56db'],
                ['Added new resident record',   'nena.torres · Resident Mgmt',      '5 min ago',   'bi-person-plus-fill',       '#f0fdf4','#16a34a'],
                ['Logged blotter entry',        'pedro.garcia · Blotter Mgmt',      '18 min ago',  'bi-journal-plus',           '#fef2f2','#dc2626'],
                ['Exported monthly report',     'admin.brgy · Reports',             '34 min ago',  'bi-file-earmark-pdf-fill',  '#fef2f2','#dc2626'],
                ['Renewed business permit',     'nena.torres · Business Permits',   '1 hr ago',    'bi-shop-window',            '#fff7ed','#ea580c'],
                ['Updated official profile',    'admin.brgy · Officials',           '2 hrs ago',   'bi-shield-fill-check',      '#ede9fe','#7c3aed'],
                ['User login · pedro.garcia',   'System · Auth',                    '2 hrs ago',   'bi-box-arrow-in-right',     '#f0fdf4','#16a34a'],
                ['Uploaded committee report',   'maria.santos · Committees',        'Yesterday',   'bi-file-earmark-arrow-up',  '#f5f3ff','#7c3aed'],
            ];
            @endphp
            @foreach($logs as $l)
            <div class="log-item">
                <div class="li-icon" style="background:{{ $l[4] }};color:{{ $l[5] }};"><i class="bi {{ $l[2] }}"></i></div>
                <div>
                    <div class="li-action">{{ $l[0] }}</div>
                    <div class="li-user">{{ $l[1] }}</div>
                </div>
                <div class="li-time">{{ $l[3] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-5">
        <div class="section-heading">Backup & Restore</div>
        <div class="backup-card mb-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="backup-indicator bi-success"></span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#0f172a;">Last Backup Successful</div>
                    <div style="font-size:12px;color:#64748b;">Feb 25, 2025 · 2:00 AM · Auto backup</div>
                </div>
            </div>
            <div style="font-size:12px;color:#94a3b8;border-top:1px solid #f1f5f9;padding-top:10px;margin-bottom:14px;">
                Backup size: <strong>84.2 MB</strong> &nbsp;·&nbsp; Storage: <strong>Google Drive</strong>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2"
                        style="border-radius:8px;font-size:13px;font-weight:600;padding:9px;">
                    <i class="bi bi-cloud-upload-fill"></i> Backup Now
                </button>
                <button class="btn btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius:8px;font-size:13px;font-weight:600;padding:9px 14px;">
                    <i class="bi bi-clock-history"></i> History
                </button>
            </div>
        </div>

        <div class="backup-card">
            <div style="font-size:13.5px;font-weight:700;color:#0f172a;margin-bottom:4px;">Restore Database</div>
            <div style="font-size:12px;color:#64748b;margin-bottom:14px;">Upload a backup file to restore the system to a previous state.</div>
            <div style="border: 2px dashed #cbd5e1; border-radius: 10px; padding: 18px; text-align: center; color: #94a3b8; font-size:13px; cursor:pointer; margin-bottom:12px;">
                <i class="bi bi-cloud-arrow-up-fill" style="font-size:24px;display:block;margin-bottom:6px;"></i>
                Drop backup file here or click to browse
            </div>
            <button class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2"
                    style="border-radius:8px;font-size:13px;font-weight:600;padding:9px;">
                <i class="bi bi-arrow-counterclockwise"></i> Restore from Backup
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        new Chart(document.getElementById('rolesChart'), {
            type: 'doughnut',
            data: {
                labels: ['Admin','Secretary','Committee','Viewer'],
                datasets: [{ data: [2,3,8,1], backgroundColor: ['#6d28d9','#1a56db','#16a34a','#94a3b8'], borderWidth: 0, hoverOffset: 4 }]
            },
            options: { cutout: '62%', maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, boxWidth: 10, padding: 10 } } }
            }
        });

        // YAJRA USER TABLE
        $(document).ready(function () {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.data') }}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'first_name', name: 'first_name'},
                    {data: 'middle_name', name: 'middle_name'},
                    {data: 'last_name', name: 'last_name'},
                    {data: 'email', name: 'email'},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'last_accessed', name: 'last_accessed'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        });
    </script>
@endsection
