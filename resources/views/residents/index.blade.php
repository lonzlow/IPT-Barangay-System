{{--
residents.blade.php
Route: GET /residents → route('residents.index')
--}}
@extends('layouts.app')

@section('title', 'Residents – Barangay Management System')
@section('page-title', 'Resident Management')

@section('styles')
    <style>
        .si-blue {
            background: #eff6ff;
            color: #1a56db;
        }

        .si-green {
            background: #f0fdf4;
            color: #16a34a;
        }

        .si-red {
            background: #fef2f2;
            color: #dc2626;
        }

        .si-amber {
            background: #fffbeb;
            color: #d97706;
        }

        .badge-active {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-deceased {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-transfer {
            background: #fef3c7;
            color: #b45309;
        }

        .search-input-group .form-control {
            border-right: none;
            font-size: 13.5px;
            border-radius: 8px 0 0 8px;
        }

        .search-input-group .btn {
            border-radius: 0 8px 8px 0;
            font-size: 13px;
        }

        .filter-chip {
            font-size: 12px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all .15s;
        }

        .filter-chip:hover,
        .filter-chip.active {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1a56db;
        }

        .res-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ra-m {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .ra-f {
            background: #fce7f3;
            color: #be185d;
        }

        .chart-wrap {
            position: relative;
            height: 220px;
        }
    </style>
@endsection

@section('content')

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-800 mb-1" style="font-size:18px;">Resident Management</h5>
            <p class="mb-0" style="font-size:13px;color:#64748b;">Manage all registered residents of the barangay.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('residents.demographics') }}" class="btn btn-outline-primary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
                <i class="bi bi-graph-up"></i> Statistics
            </a>
            <a href="{{ route('residents.create') }}" class="btn btn-primary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
                <i class="bi bi-person-plus-fill"></i> Add New Resident
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── STAT WIDGETS ── --}}
    <div class="section-heading">Overview</div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3 h-100">
                <div class="stat-icon si-blue"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($totalResidents) }}</div>
                    <div class="stat-label">Total Residents</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3 h-100">
                <div class="stat-icon si-green"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($activeCount) }}</div>
                    <div class="stat-label">Active Residents</div>
                    <span class="stat-badge badge-active">{{ $activePercentage }}%</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3 h-100">
                <div class="stat-icon si-red"><i class="bi bi-person-dash-fill"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($deceasedCount) }}</div>
                    <div class="stat-label">Deceased</div>
                    <span class="stat-badge badge-deceased">{{ $deceasedPercentage }}%</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3 h-100">
                <div class="stat-icon si-amber"><i class="bi bi-person-walking"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($transferredCount) }}</div>
                    <div class="stat-label">Transferred</div>
                    <span class="stat-badge badge-transfer">{{ $transferredPercentage }}%</span>
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
                        <span
                            style="width:10px;height:10px;border-radius:2px;background:#1a56db;display:inline-block;"></span>
                        Registered
                    </div>
                    <div class="d-flex align-items-center gap-1" style="font-size:12px;">
                        <span
                            style="width:10px;height:10px;border-radius:2px;background:#e2e8f0;display:inline-block;"></span>
                        Not Registered
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── RESIDENT LIST ── --}}
    <div class="section-heading">Resident List</div>

    {{-- Filter Bar --}}
    <div class="collapse mb-3" id="filterPanel">
        <div style="background:#f9fafb;padding:12px;border-radius:8px;border:1px solid #e2e8f0;">
            <div class="row g-2">
                <div class="col-md-2">
                    <label
                        style="font-size:11px;color:#94a3b8;font-weight:600;display:block;margin-bottom:4px;">Gender</label>
                    <select id="gender-filter" class="form-select form-select-sm" style="font-size:12px;border-radius:6px;"
                        onchange="filterTable()">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label
                        style="font-size:11px;color:#94a3b8;font-weight:600;display:block;margin-bottom:4px;">Residency</label>
                    <select id="residency-filter" class="form-select form-select-sm"
                        style="font-size:12px;border-radius:6px;" onchange="filterTable()">
                        <option value="">All</option>
                        <option value="Active">Active</option>
                        <option value="Deceased">Deceased</option>
                        <option value="Transferred">Transferred</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label style="font-size:11px;color:#94a3b8;font-weight:600;display:block;margin-bottom:4px;">Voter
                        Status</label>
                    <select id="voter-filter" class="form-select form-select-sm" style="font-size:12px;border-radius:6px;"
                        onchange="filterTable()">
                        <option value="">All</option>
                        <option value="Registered">Registered</option>
                        <option value="Unregistered">Unregistered</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label style="font-size:11px;color:#94a3b8;font-weight:600;display:block;margin-bottom:4px;">Civil
                        Status</label>
                    <select id="civil-status-filter" class="form-select form-select-sm"
                        style="font-size:12px;border-radius:6px;" onchange="filterTable()">
                        <option value="">All</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Separated">Separated</option>
                        <option value="Divorced">Divorced</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label style="font-size:11px;color:#94a3b8;font-weight:600;display:block;margin-bottom:4px;">Age
                        Range</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="number" id="age-from" class="form-control form-control-sm" placeholder="From"
                            style="font-size:12px;" min="0" max="120" onchange="filterTable()">
                        <span style="color:#94a3b8;">−</span>
                        <input type="number" id="age-to" class="form-control form-control-sm" placeholder="To"
                            style="font-size:12px;" min="0" max="120" onchange="filterTable()">
                        <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()"
                            style="font-size:11px;border-radius:6px;white-space:nowrap;"><i class="bi bi-x"></i>
                            Clear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <span class="heading">Residents <span class="badge bg-light text-secondary fw-600 ms-1"
                        style="font-size:12px;">4,821</span></span>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        style="font-size:12.5px;border-radius:7px;" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                    <a href="{{ route('residents.exportPDF') }}"
                        class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        style="font-size:12.5px;border-radius:7px;">
                        <i class="bi bi-download"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="display" id="residents-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:8%;">ID</th>
                        <th style="width:9%;">First Name</th>
                        <th style="width:5%;">M.I.</th>
                        <th style="width:9%;">Last Name</th>
                        <th style="width:6%;">Suffix</th>
                        <th style="width:5%;">Age</th>
                        <th style="width:12%;">Email</th>
                        <th style="width:10%;">Contact No.</th>
                        <th style="width:7%;">Gender</th>
                        <th style="width:13%;">Address / Purok</th>
                        <th style="width:8%;">Voter</th>
                        <th style="width:8%;">Status</th>
                        <th style="width:8%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div
            style="padding:12px 16px;border-top:1px solid #f1f5f9;font-size:12px;color:#64748b;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
            <span id="table-info">Showing 0 entries</span>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{ data: [2310, 2180], backgroundColor: ['#1a56db', '#f472b6'], borderWidth: 0, hoverOffset: 6 }]
            },
            options: {
                cutout: '68%', maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 }, boxWidth: 10, padding: 14 } } }
            }
        });

        new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: ['0–12', '13–17', '18–24', '25–34', '35–49', '50–64', '65+'],
                datasets: [{ label: 'Residents', data: [620, 410, 590, 840, 970, 760, 631], backgroundColor: '#1a56db', borderRadius: 6, borderSkipped: false }]
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
                labels: ['Registered', 'Not Registered'],
                datasets: [{ data: [2850, 820], backgroundColor: ['#1a56db', '#e2e8f0'], borderWidth: 0, hoverOffset: 4 }]
            },
            options: { cutout: '72%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Filter table function
        function filterTable() {
            table.draw();
        }

        // Clear all filters
        function clearFilters() {
            document.getElementById('gender-filter').value = '';
            document.getElementById('residency-filter').value = '';
            document.getElementById('voter-filter').value = '';
            document.getElementById('civil-status-filter').value = '';
            document.getElementById('age-from').value = '';
            document.getElementById('age-to').value = '';
            filterTable();
        }

        // YAJRA RESIDENTS TABLE
        var table;
        $(document).ready(function () {
            table = $('#residents-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('residents.data') }}",
                    data: function (d) {
                        d.gender = document.getElementById('gender-filter').value;
                        d.residency_status = document.getElementById('residency-filter').value;
                        d.voter_status = document.getElementById('voter-filter').value;
                        d.civil_status = document.getElementById('civil-status-filter').value;
                        d.age_from = document.getElementById('age-from').value;
                        d.age_to = document.getElementById('age-to').value;
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'first_name', name: 'first_name' },
                    { data: 'middle_name', name: 'middle_name' },
                    { data: 'last_name', name: 'last_name' },
                    { data: 'suffix', name: 'suffix' },
                    { data: 'age', name: 'age', orderable: false, searchable: false },
                    { data: 'email', name: 'email' },
                    { data: 'contact_number', name: 'contact_number' },
                    { data: 'gender', name: 'gender' },
                    { data: 'household_purok', name: 'household_purok' },
                    { data: 'voter', name: 'voter', orderable: false, searchable: false },
                    { data: 'residency', name: 'residency', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection