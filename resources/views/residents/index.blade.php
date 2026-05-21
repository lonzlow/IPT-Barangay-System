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

        .row-deleted {
            background-color: #e2e8f0 !important;
            /* Light gray background */
            color: #64748b !important;
            /* Muted text color */
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
                    <div class="stat-value" id="stat-total">{{ number_format($totalResidents) }}</div>
                    <div class="stat-label">Total Residents</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3 h-100">
                <div class="stat-icon si-green"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <div class="stat-value" id="stat-active">{{ number_format($activeCount) }}</div>
                    <div class="stat-label">Active Residents</div>
                    <span class="stat-badge badge-active">{{ $activePercentage }}%</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3 h-100">
                <div class="stat-icon si-red"><i class="bi bi-person-dash-fill"></i></div>
                <div>
                    <div class="stat-value" id="stat-deceased">{{ number_format($deceasedCount) }}</div>
                    <div class="stat-label">Deceased</div>
                    <span class="stat-badge badge-deceased">{{ $deceasedPercentage }}%</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3 h-100">
                <div class="stat-icon si-amber"><i class="bi bi-person-walking"></i></div>
                <div>
                    <div class="stat-value" id="stat-transferred">{{ number_format($transferredCount) }}</div>
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
                    <label style="font-size:11px; color:#94a3b8; font-weight:600; display:block; margin-bottom:4px;">
                        Age Range
                    </label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="number" id="age-from" class="form-control form-control-sm" placeholder="From"
                            style="font-size:13px; height: 42px !important; border-radius:6px; box-shadow: none;" min="0"
                            max="120" onchange="filterTable()">

                        <span style="color:#94a3b8; font-weight: bold;">−</span>

                        <input type="number" id="age-to" class="form-control form-control-sm" placeholder="To"
                            style="font-size:13px; height: 42px !important; border-radius:6px; box-shadow: none;" min="0"
                            max="120" onchange="filterTable()">

                        <button
                            class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center"
                            onclick="clearFilters()"
                            style="font-size:13px; height: 42px !important; border-radius:6px; white-space:nowrap; gap: 4px; box-shadow: none; padding: 0 14px;">
                            <i class="bi bi-x" style="font-size: 16px; line-height: 1;"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <span class="heading">Residents <span class="badge bg-light text-secondary fw-600 ms-1"
                        style="font-size:12px;">{{ number_format($totalResidents) }}</span></span>
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
            <table id="residents-table" class="table table-hover align-middle mb-0" style="width:100%; font-size: 13px;">
                <thead class="bg-light"
                    style="color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>First Name</th>
                        <th>M.I.</th>
                        <th>Last Name</th>
                        <th>Suffix</th>
                        <th>Age</th>
                        <th>Email</th>
                        <th>Contact No.</th>
                        <th>Gender</th>
                        <th>Address / Purok</th>
                        <th>Voter</th>
                        <th>Status</th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody style="color: #334155;">
                </tbody>
            </table>
        </div>
        <div
            style="padding:12px 16px; border-top:1px solid #f1f5f9; font-size:12px; color:#64748b; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 12px;">
            <span id="table-info" style="font-weight: 500;">Showing 0 entries</span>

            <nav id="custom-pagination" aria-label="Table navigation">
            </nav>
        </div>
    </div>


@endsection
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill me-2"></i> Resident updated successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Resident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this resident?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Resident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to restore this resident?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">Restore</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>

        let genderChart, ageChart, voterChart;

        $(document).ready(function () {
            // 1. CHART INITIALIZATIONS (No document ready needed)
            genderChart = new Chart(document.getElementById('genderChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Male', 'Female'],
                    datasets: [{ data: [{{ $maleCount }}, {{ $femaleCount }}], backgroundColor: ['#1a56db', '#f472b6'], borderWidth: 0, hoverOffset: 6 }]
                },
                options: { cutout: '68%', maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 }, boxWidth: 10, padding: 14 } } } }
            });

            ageChart = new Chart(document.getElementById('ageChart'), {
                type: 'bar',
                data: {
                    labels: ['0–12', '13–17', '18–24', '25–34', '35–49', '50–64', '65+'],
                    datasets: [{ label: 'Residents', data: {!! json_encode($ageData) !!}, backgroundColor: '#1a56db', borderRadius: 6, borderSkipped: false }]
                },
                options: { maintainAspectRatio: false, scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' } } }, plugins: { legend: { display: false } } }
            });

            voterChart = new Chart(document.getElementById('voterChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Registered', 'Not Registered'],
                    datasets: [{ data: [{{ $registeredVoters }}, {{ $unregisteredVoters }}], backgroundColor: ['#1a56db', '#e2e8f0'], borderWidth: 0, hoverOffset: 4 }]
                },
                options: { cutout: '72%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        });

        // 2. GLOBAL FUNCTIONS (Para ma-access ng HTML onclick)
        let deleteId = null;
        function confirmDelete(id) {
            deleteId = id;
            $('#deleteConfirmModal').modal('show');
        }

        let restoreId = null;
        function confirmRestore(id) {
            restoreId = id;
            $('#restoreConfirmModal').modal('show');
        }

        function openEditModal(id) {
            let url = "{{ route('residents.edit', ':id') }}".replace(':id', id);
            $.ajax({
                url: url, type: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (data) {
                    $('#edit_resident_id').val(data.id);
                    $('#edit_first_name').val(data.first_name);
                    $('#edit_middle_name').val(data.middle_name);
                    $('#edit_last_name').val(data.last_name);
                    $('#edit_suffix').val(data.suffix);
                    $('#edit_birthdate').val(data.birthdate);
                    $('#edit_gender').val(data.gender);
                    $('#edit_civil_status').val(data.civil_status);
                    $('#edit_email').val(data.email);
                    $('#edit_contact_number').val(data.contact_number);
                    $('#edit_voter_status').val(data.voter_status);
                    $('#edit_residency_status').val(data.residency_status);
                    $('#editResidentModal').modal('show');
                }
            });
        }

        // 3. MAIN LOGIC
        $(document).ready(function () {
            var table = $('#residents-table').DataTable({
                processing: true, serverSide: true, pageLength: 10, lengthChange: false, searching: true, dom: 't',
                ajax: {
                    url: "{{ route('residents.data') }}",
                    data: function (d) {
                        d.gender = $('#gender-filter').val();
                        d.residency_status = $('#residency-filter').val();
                        d.voter_status = $('#voter-filter').val();
                        d.civil_status = $('#civil-status-filter').val();
                        d.age_from = $('#age-from').val();
                        d.age_to = $('#age-to').val();
                    }
                },
                createdRow: function (row, data) {
                    if (data.deleted_at !== null) {
                        $(row).addClass('row-deleted');
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'first_name', name: 'first_name' },
                    { data: 'middle_name', name: 'middle_name' },
                    { data: 'last_name', name: 'last_name' },
                    { data: 'suffix', name: 'suffix' },
                    { data: 'age', name: 'age' },
                    { data: 'email', name: 'email' },
                    { data: 'contact_number', name: 'contact_number' },
                    { data: 'gender', name: 'gender' },
                    { data: 'household_purok', name: 'household_purok' },
                    { data: 'voter', name: 'voter' },
                    { data: 'civil_status', name: 'civil_status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                drawCallback: function (settings) {
                    var api = this.api();
                    var pageInfo = api.page.info();

                    // 1. UPDATE LIVE COUNTER
                    if (pageInfo.recordsTotal > 0) {
                        var startEntry = pageInfo.start + 1;
                        var endEntry = pageInfo.end;
                        var totalEntries = pageInfo.recordsDisplay;
                        $('#table-info').text('Showing ' + startEntry + ' to ' + endEntry + ' of ' + totalEntries + ' entries');
                    } else {
                        $('#table-info').text('Showing 0 entries');
                    }

                    // 2. CUSTOM PAGINATION UI
                    var navContainer = $('#custom-pagination');
                    navContainer.empty();
                    if (pageInfo.pages <= 1) return;

                    var ul = $('<ul class="pagination pagination-sm mb-0 d-flex align-items-center" style="gap: 4px;"></ul>');
                    var prevClass = (pageInfo.page === 0) ? 'disabled' : '';
                    ul.append($('<li class="page-item ' + prevClass + '"><a class="page-link px-2 py-1 text-secondary border" href="#" data-page="prev" style="border-radius: 6px; font-size: 11.5px; font-weight: 500; background: #fff; box-shadow: none;">Previous</a></li>'));

                    var startPage = Math.max(0, pageInfo.page - 2);
                    var endPage = Math.min(pageInfo.pages - 1, startPage + 4);
                    if (endPage - startPage < 4) startPage = Math.max(0, endPage - 4);

                    for (var i = startPage; i <= endPage; i++) {
                        var activeClass = (pageInfo.page === i) ? 'active' : '';
                        var activeStyle = (pageInfo.page === i) ? 'background-color: #1a56db; border-color: #1a56db; color: #fff; font-weight: 600;' : 'background: #fff; color: #475569;';
                        ul.append($('<li class="page-item ' + activeClass + '"><a class="page-link d-inline-flex align-items-center justify-content-center border" href="#" data-page="' + i + '" style="border-radius: 6px; width: 28px; height: 28px; font-size: 11.5px; box-shadow: none; ' + activeStyle + '">' + (i + 1) + '</a></li>'));
                    }

                    var nextClass = (pageInfo.page === pageInfo.pages - 1) ? 'disabled' : '';
                    ul.append($('<li class="page-item ' + nextClass + '"><a class="page-link px-2 py-1 text-secondary border" href="#" data-page="next" style="border-radius: 6px; font-size: 11.5px; font-weight: 500; background: #fff; box-shadow: none;">Next</a></li>'));
                    navContainer.append(ul);

                    $('#residents-table tbody tr').each(function () {
                        if ($(this).text().includes('Deleted')) $(this).addClass('row-deleted');
                    });
                }
            });

            // Event: Delete
            $('#confirmDeleteBtn').on('click', function () {
                if (!deleteId) return;
                $.ajax({
                    url: "/residents/" + deleteId, type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function () {
                        $('#deleteConfirmModal').modal('hide');
                        table.ajax.reload(null, false);
                        refreshDashboard();
                        showToast('Resident deleted successfully!');
                    }
                });
            });

            // Event: Restore
            $('#confirmRestoreBtn').on('click', function () {
                if (!restoreId) return;
                $.ajax({
                    url: "/residents/" + restoreId + "/restore", type: 'POST',
                    data: { _token: "{{ csrf_token() }}", _method: "PATCH" },
                    success: function () {
                        $('#restoreConfirmModal').modal('hide');
                        table.ajax.reload(null, false);
                        refreshDashboard();
                        showToast('Resident restored successfully!');
                    }
                });
            });

            // Event: Edit Form Submit
            $('#editResidentForm').on('submit', function (e) {
                e.preventDefault();
                let id = $('#edit_resident_id').val();
                $.ajax({
                    url: "{{ route('residents.update', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: $(this).serialize() + "&_method=PUT",
                    success: function () {
                        $('#editResidentModal').modal('hide');
                        table.ajax.reload(null, false);
                        refreshDashboard();
                        showToast('Resident updated successfully!');
                    }
                });
            });

            function showToast(message) {
                var toastEl = document.getElementById('successToast');
                toastEl.querySelector('.toast-body').innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> ' + message;
                new bootstrap.Toast(toastEl).show();
            }
        });

        // Function para i-update ang lahat
        function refreshDashboard() {
            $.get("{{ route('residents.stats.refresh') }}", function (data) {
                // 1. Update Numbers
                $('#stat-total').text(data.totalResidents);
                $('#stat-active').text(data.activeCount);
                $('#stat-deceased').text(data.deceasedCount);
                $('#stat-transferred').text(data.transferredCount);

                // Update Badges
                $('.badge-active').text(data.activePercentage + '%');
                $('.badge-deceased').text(data.deceasedPercentage + '%');
                $('.badge-transfer').text(data.transferredPercentage + '%');

                // 2. Update Charts (Assuming genderChart, ageChart, voterChart are global)
                genderChart.data.datasets[0].data = data.genderData;
                genderChart.update();

                ageChart.data.datasets[0].data = data.ageData;
                ageChart.update();

                voterChart.data.datasets[0].data = data.voterData;
                voterChart.update();

                // 3. Update Table Header Counter
                $('.table-header .heading .badge').text(data.totalResidents);
            });
        }
    </script>
    <div class="modal fade" id="editResidentModal" tabindex="-1" aria-labelledby="editResidentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: 12px; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
                <div class="modal-header px-4 pt-4 pb-2" style="border: none;">
                    <h5 class="modal-title fw-bold text-dark" id="editResidentModalLabel"
                        style="font-size: 16px; letter-spacing: -0.025em;">Edit Resident Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="box-shadow: none; font-size: 12px;"></button>
                </div>
                <form id="editResidentForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_resident_id">

                    <div class="modal-body px-4 pb-4 pt-2" style="font-size: 13px; color: #475569;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">First
                                    Name</label>
                                <input type="text" id="edit_first_name" name="first_name"
                                    class="form-control form-control-sm" required
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                            </div>
                            <div class="col-md-3">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Middle
                                    Name</label>
                                <input type="text" id="edit_middle_name" name="middle_name"
                                    class="form-control form-control-sm"
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                            </div>
                            <div class="col-md-3">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Last
                                    Name</label>
                                <input type="text" id="edit_last_name" name="last_name" class="form-control form-control-sm"
                                    required
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                            </div>
                            <div class="col-md-2">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Suffix</label>
                                <input type="text" id="edit_suffix" name="suffix" class="form-control form-control-sm"
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                            </div>
                            <div class="col-md-4">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Birthdate</label>
                                <input type="date" id="edit_birthdate" name="birthdate" class="form-control form-control-sm"
                                    required
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                            </div>
                            <div class="col-md-4">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Gender</label>
                                <select id="edit_gender" name="gender" class="form-select form-select-sm"
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Civil
                                    Status</label>
                                <select id="edit_civil_status" name="civil_status" class="form-select form-select-sm"
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                    <option value="Divorced">Divorced</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Email
                                    Address</label>
                                <input type="email" id="edit_email" name="email" class="form-control form-control-sm"
                                    required
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                            </div>
                            <div class="col-md-6">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Contact
                                    Number</label>
                                <input type="text" id="edit_contact_number" name="contact_number"
                                    class="form-control form-control-sm" required
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                            </div>
                            <div class="col-md-6">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Voter
                                    Status</label>
                                <select id="edit_voter_status" name="voter_status" class="form-select form-select-sm"
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                                    <option value="Registered">Registered</option>
                                    <option value="Unregistered">Unregistered</option>
                                    <option value="Suspended">Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="mb-1"
                                    style="font-weight: 600; color: #64748b; font-size: 11px; uppercase; letter-spacing: 0.05em;">Residency
                                    Status</label>
                                <select id="edit_residency_status" name="residency_status"
                                    class="form-select form-select-sm"
                                    style="border-radius: 6px; border-color: #cbd5e1; height: 36px; box-shadow: none;">
                                    <option value="Active">Active</option>
                                    <option value="Deceased">Deceased</option>
                                    <option value="Transferred">Transferred</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer px-4 pb-4 pt-2" style="border: none;">
                        <button type="button" class="btn btn-sm text-secondary border-0" data-bs-dismiss="modal"
                            style="font-weight: 600; font-size: 12px; padding: 8px 16px;">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary"
                            style="font-weight: 600; font-size: 12px; padding: 8px 20px; border-radius: 6px; background-color: #1a56db; border: none;">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection