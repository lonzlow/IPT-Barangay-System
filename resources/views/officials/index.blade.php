@extends("layouts.app")

@section("title", "Officials & Staff - Barangay Management System")
@section("page-title", "Officials & Staff Management")

@section("styles")
<style>
    .officials-page .official-card {
        position: relative; min-height: 214px; background: #fff; border: 1px solid #e2e8f0;
        border-radius: 14px; padding: 28px 18px 20px; overflow: hidden; transition: all .18s ease;
    }
    .officials-page .official-card:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(15,23,42,.08); }
    .officials-page .official-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--accent, #1a56db);
    }
    .officials-page .official-avatar {
        width: 68px; height: 68px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 800; color: var(--accent, #1a56db); background: var(--accent-soft, #dbeafe);
    }
    .officials-page .official-name { font-size: 14px; font-weight: 800; color: #020617; line-height: 1.25; }
    .officials-page .official-role, .officials-page .official-term, .officials-page .official-email {
        font-size: 12px; color: #64748b;
    }
    .officials-page .official-actions .btn {
        border: 0; background: #f8fafc; color: #020617; border-radius: 8px; font-size: 12px; padding: 5px 10px;
    }
    .officials-page .official-actions .btn:hover { background: #e2e8f0; }
    .officials-page .id-preview {
        background: #172b49; color: #e0f2fe; min-height: 204px; border-radius: 14px; padding: 26px;
        position: relative; overflow: hidden;
    }
    .officials-page .id-preview::after {
        content: ""; position: absolute; width: 190px; height: 190px; border-radius: 50%; right: -42px; top: -42px;
        background: rgba(148, 163, 184, .12);
    }
    .officials-page .id-kicker { font-size: 11px; font-weight: 800; letter-spacing: 1.8px; color: #93c5fd; }
    .officials-page .id-name { font-size: 19px; font-weight: 800; color: #fff; margin-top: 14px; }
    .officials-page .id-meta { font-size: 12px; color: #bfdbfe; margin-top: 7px; }
    .officials-page .id-qr {
        position: absolute; right: 18px; bottom: 18px; width: 64px; height: 64px; border-radius: 8px;
        background: rgba(255,255,255,.14); display: flex; align-items: center; justify-content: center;
        color: #cbd5e1; font-size: 28px; z-index: 1;
    }
    .officials-page .chart-wrap { position: relative; height: 230px; }
    .officials-page .term-row { margin-bottom: 17px; }
    .officials-page .term-label { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .officials-page .progress { height: 6px; background: #e2e8f0; border-radius: 999px; }
    .officials-page .progress-bar { border-radius: 999px; }
    .officials-page .form-control, .officials-page .form-select { border-radius: 8px; font-size: 13px; }
    .officials-page .form-label { font-size: 12.5px; font-weight: 700; color: #0f172a; }
    .officials-page .detail-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: .5px; }
    .officials-page .detail-value { font-size: 13px; font-weight: 700; color: #0f172a; }
</style>
@endsection

@section("content")
@php
    $palette = [
        ['accent' => '#1a56db', 'soft' => '#dbeafe'],
        ['accent' => '#7c3aed', 'soft' => '#ede9fe'],
        ['accent' => '#0f9f6e', 'soft' => '#ccfbf1'],
        ['accent' => '#ea580c', 'soft' => '#ffedd5'],
        ['accent' => '#16a34a', 'soft' => '#dcfce7'],
        ['accent' => '#db2777', 'soft' => '#fce7f3'],
        ['accent' => '#d97706', 'soft' => '#fef3c7'],
        ['accent' => '#0369a1', 'soft' => '#e0f2fe'],
        ['accent' => '#9333ea', 'soft' => '#f3e8ff'],
        ['accent' => '#64748b', 'soft' => '#f1f5f9'],
    ];
    $previewResident = $idPreviewOfficial?->resident;
    $previewName = $previewResident ? trim(collect([$previewResident->first_name, $previewResident->middle_name, $previewResident->last_name, $previewResident->suffix])->filter()->join(' ')) : 'No official selected';
@endphp

<div class="officials-page">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-800 mb-1" style="font-size:18px;">Officials & Staff Management</h5>
            <p class="mb-0" style="font-size:13px;color:#64748b;">Manage barangay officials, staff profiles, role assignments, and term tracking.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary d-flex align-items-center gap-2" onclick="openDesignationModal()"
                    style="border-radius:8px;font-size:13.5px;font-weight:700;padding:9px 16px;">
                <i class="bi bi-person-vcard"></i> Assign Designation
            </button>
            <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
                    style="border-radius:8px;font-size:13.5px;font-weight:700;padding:9px 18px;">
                <i class="bi bi-person-plus-fill"></i> Add Official Profile
            </button>
        </div>
    </div>

    <div id="officialAlert" class="mb-3"></div>

    <div class="section-heading">Overview</div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#eff6ff;color:#1a56db;"><i class="bi bi-shield-fill-check"></i></div>
                <div><div class="stat-value">{{ $summary['total'] }}</div><div class="stat-label">Total Officials & Staff</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#f3e8ff;color:#7c3aed;"><i class="bi bi-people-fill"></i></div>
                <div><div class="stat-value">{{ $summary['kagawads'] }}</div><div class="stat-label">Barangay Kagawads</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-calendar-check-fill"></i></div>
                <div><div class="stat-value">{{ $summary['term_years'] }} yrs</div><div class="stat-label">Current Term Duration</div><span class="stat-badge" style="background:#bbf7d0;color:#15803d;">{{ $summary['term_label'] }}</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fffbeb;color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
                <div class="flex-grow-1">
                    <div class="stat-value">{{ $summary['term_months_left'] }} mos</div>
                    <div class="stat-label">Until Term Ends</div>
                    <div class="progress mt-2" style="height:6px;"><div class="progress-bar" style="width:{{ $summary['term_progress'] }}%;"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-heading">Barangay Officials</div>
    <div class="row g-3 mb-4">
        @forelse($officials as $official)
            @php
                $resident = $official->resident;
                $fullName = $resident ? trim(collect([$resident->first_name, $resident->middle_name, $resident->last_name, $resident->suffix])->filter()->join(' ')) : 'Unlinked Resident';
                $words = preg_split('/\s+/', trim($fullName));
                $initials = count($words) > 1 ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1)) : strtoupper(substr($fullName, 0, 2));
                $colors = $palette[$loop->index % count($palette)];
                $assignment = $official->assignments->first();
                $roleLabel = $official->role?->role_name ?? 'Unassigned Role';
                $subRole = $assignment ? $roleLabel . ' - ' . $assignment->committee?->name : $roleLabel;
                $termLabel = $official->term_end ? $official->term_start?->format('Y') . '-' . $official->term_end?->format('Y') : 'Appointive';
            @endphp
            <div class="col-md-6 col-xl-3">
                <div class="official-card text-center h-100" style="--accent:{{ $colors['accent'] }};--accent-soft:{{ $colors['soft'] }};">
                    <div class="official-avatar mb-3">{{ $initials }}</div>
                    <div class="official-name">HON. {{ strtoupper($fullName) === $fullName ? $fullName : Str::title($fullName) }}</div>
                    <div class="official-role mt-1">{{ $subRole }}</div>
                    <div class="official-term mt-2"><i class="bi bi-calendar3 me-1"></i>{{ $termLabel }}</div>
                    @if($official->user?->email)
                        <div class="official-email mt-1 text-truncate">{{ $official->user->email }}</div>
                    @endif
                    <div class="official-actions d-flex justify-content-center gap-2 mt-3">
                        <button class="btn" onclick="viewOfficial('{{ $official->id }}')"><i class="bi bi-eye me-1"></i>View</button>
                        <button class="btn" onclick="previewId('{{ $official->id }}')"><i class="bi bi-card-heading me-1"></i>ID</button>
                        <a class="btn" href="{{ route('officials.digitalId', $official) }}" target="_blank" rel="noopener"><i class="bi bi-printer me-1"></i>Print</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="chart-card text-center py-5">
                    <div class="fw-800 mb-1">No officials or staff profiles yet.</div>
                    <div class="text-muted" style="font-size:13px;">Create a profile from an existing resident and assign a role.</div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="section-heading">Digital ID Preview</div>
            <div class="id-preview" id="idPreviewCard">
                <div class="id-kicker">BARANGAY NEW ERA - OFFICIAL ID</div>
                <div class="id-name" id="idPreviewName">{{ $previewName ? 'HON. ' . Str::upper($previewName) : 'OFFICIAL PROFILE' }}</div>
                <div class="id-meta" id="idPreviewRole">{{ $idPreviewOfficial?->role?->role_name ?? 'Role not assigned' }}</div>
                <div class="id-meta">ID No.: <span id="idPreviewNumber">{{ $idPreviewOfficial?->official_number ?? 'Pending' }}</span></div>
                <div class="id-meta">Term: <span id="idPreviewTerm">{{ $idPreviewOfficial ? ($idPreviewOfficial->term_end ? $idPreviewOfficial->term_start?->format('Y') . '-' . $idPreviewOfficial->term_end?->format('Y') : 'Appointive') : 'Pending' }}</span></div>
                <div class="id-qr"><i class="bi bi-qr-code"></i></div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-outline-primary flex-grow-1" onclick="window.print()" style="border-radius:8px;font-size:13px;font-weight:700;"><i class="bi bi-printer-fill me-1"></i>Print ID</button>
                <button class="btn btn-outline-secondary" onclick="downloadIdPreview()" style="border-radius:8px;"><i class="bi bi-download"></i></button>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="section-heading">Designation Breakdown</div>
            <div class="chart-card h-100">
                <div class="chart-wrap"><canvas id="designationChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="section-heading">Term Duration Tracker</div>
            <div class="chart-card h-100">
                @forelse($termTrackers as $index => $tracker)
                    @php $barColor = $palette[$index % count($palette)]['accent']; @endphp
                    <div class="term-row">
                        <div class="term-label"><span>{{ $tracker['label'] }}</span><span style="color:{{ $barColor }};">{{ $tracker['percentage'] }}%</span></div>
                        <div class="progress"><div class="progress-bar" style="background:{{ $barColor }};width:{{ $tracker['percentage'] }}%;"></div></div>
                    </div>
                @empty
                    <p class="text-muted mb-0" style="font-size:13px;">No role data available for term tracking.</p>
                @endforelse
                <div class="text-muted mt-4" style="font-size:12px;">Term completion as of {{ now()->format('F Y') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="officialModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Official Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="officialForm">
                @csrf
                <input type="hidden" id="officialId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Resident</label>
                            <select class="form-select" name="resident_id" required>
                                <option value="">Select resident</option>
                                @foreach($residents as $resident)
                                    <option value="{{ $resident->id }}">{{ trim($resident->last_name . ', ' . $resident->first_name . ' ' . $resident->middle_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role_id" required>
                                <option value="">Select role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Official ID Number</label>
                            <input type="text" class="form-control" name="official_number" placeholder="Auto-generated when blank">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Term Start</label>
                            <input type="date" class="form-control" name="term_start" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Term End</label>
                            <input type="date" class="form-control" name="term_end">
                        </div>
                        <div class="col-12">
                            <label class="form-check-label fw-700" style="font-size:13px;">
                                <input type="checkbox" class="form-check-input me-1" name="is_active" value="1" checked> Active profile
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="designationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Assign Designation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="designationForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Official or Staff</label>
                        <select class="form-select" name="official_id" required>
                            <option value="">Select official</option>
                            @foreach($officials as $official)
                                @php $r = $official->resident; @endphp
                                <option value="{{ $official->id }}">{{ $r ? trim($r->last_name . ', ' . $r->first_name) : $official->official_number }} ({{ $official->role?->role_name ?? 'No role' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Committee / Assignment Area</label>
                        <select class="form-select" name="committee_id" required>
                            <option value="">Select committee</option>
                            @foreach($committees as $committee)
                                <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" class="form-control" name="designation" placeholder="Chairperson, Member, Coordinator" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Official Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="officialDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editFromDetailBtn">Edit Profile</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
const officials = @json($officials->values());
const roleBreakdown = @json($roleBreakdown);
const palette = @json(array_column($palette, 'accent'));
const officialModal = new bootstrap.Modal(document.getElementById('officialModal'));
const designationModal = new bootstrap.Modal(document.getElementById('designationModal'));
const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
let currentOfficialId = null;

new Chart(document.getElementById('designationChart'), {
    type: 'doughnut',
    data: {
        labels: roleBreakdown.map(item => item.label),
        datasets: [{ data: roleBreakdown.map(item => item.count), backgroundColor: palette, borderWidth: 0 }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '55%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
    }
});

function showAlert(message, type = 'success') {
    document.getElementById('officialAlert').innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
}

function openCreateModal() {
    currentOfficialId = null;
    document.getElementById('officialForm').reset();
    document.getElementById('officialId').value = '';
    document.querySelector('[name="is_active"]').checked = true;
    document.querySelector('#officialModal .modal-title').textContent = 'Add Official Profile';
    officialModal.show();
}

function openDesignationModal(officialId = '') {
    document.getElementById('designationForm').reset();
    if (officialId) document.querySelector('#designationForm [name="official_id"]').value = officialId;
    designationModal.show();
}

async function editOfficial(id) {
    currentOfficialId = id;
    const res = await fetch(`/officials/${id}/edit`, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (!data.success) return;

    const o = data.data;
    document.getElementById('officialId').value = id;
    document.querySelector('[name="resident_id"]').value = o.resident_id ?? '';
    document.querySelector('[name="role_id"]').value = o.role_id ?? '';
    document.querySelector('[name="official_number"]').value = o.official_number ?? '';
    document.querySelector('[name="term_start"]').value = o.term_start ? o.term_start.substring(0, 10) : '';
    document.querySelector('[name="term_end"]').value = o.term_end ? o.term_end.substring(0, 10) : '';
    document.querySelector('[name="is_active"]').checked = Boolean(o.is_active);
    document.querySelector('#officialModal .modal-title').textContent = 'Edit Official Profile';
    detailModal.hide();
    officialModal.show();
}

async function viewOfficial(id) {
    const res = await fetch(`/officials/${id}`, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (!data.success) return;
    const o = data.data;
    const name = [o.resident?.first_name, o.resident?.middle_name, o.resident?.last_name, o.resident?.suffix].filter(Boolean).join(' ') || 'Unlinked Resident';
    const assignments = (o.assignments || []).map(item => `
        <span class="badge text-bg-light me-1 mb-1">${item.committee?.name || 'Committee'} - ${item.designation}</span>
    `).join('') || '<span class="text-muted">No designations assigned.</span>';

    document.getElementById('officialDetailBody').innerHTML = `
        <div class="row g-3">
            <div class="col-md-6"><div class="detail-label">Name</div><div class="detail-value">${name}</div></div>
            <div class="col-md-6"><div class="detail-label">Role</div><div class="detail-value">${o.role?.role_name || 'Unassigned'}</div></div>
            <div class="col-md-6"><div class="detail-label">Official Number</div><div class="detail-value">${o.official_number || 'Pending'}</div></div>
            <div class="col-md-6"><div class="detail-label">User Account</div><div class="detail-value">${o.user?.email || 'No linked user'}</div></div>
            <div class="col-md-6"><div class="detail-label">Term Start</div><div class="detail-value">${formatDate(o.term_start)}</div></div>
            <div class="col-md-6"><div class="detail-label">Term End</div><div class="detail-value">${o.term_end ? formatDate(o.term_end) : 'Appointive'}</div></div>
            <div class="col-12"><div class="detail-label mb-2">Assignments & Designations</div>${assignments}</div>
        </div>`;
    document.getElementById('editFromDetailBtn').onclick = () => editOfficial(id);
    detailModal.show();
}

function previewId(id) {
    const o = officials.find(item => item.id === id);
    if (!o) return;
    const name = [o.resident?.first_name, o.resident?.middle_name, o.resident?.last_name, o.resident?.suffix].filter(Boolean).join(' ') || 'Unlinked Resident';
    document.getElementById('idPreviewName').textContent = `HON. ${name.toUpperCase()}`;
    document.getElementById('idPreviewRole').textContent = o.role?.role_name || 'Unassigned role';
    document.getElementById('idPreviewNumber').textContent = o.official_number || 'Pending';
    document.getElementById('idPreviewTerm').textContent = o.term_end ? `${new Date(o.term_start).getFullYear()}-${new Date(o.term_end).getFullYear()}` : 'Appointive';
    document.getElementById('idPreviewCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function downloadIdPreview() {
    const text = [
        'Barangay New Era - Official ID',
        document.getElementById('idPreviewName').textContent,
        document.getElementById('idPreviewRole').textContent,
        `ID No.: ${document.getElementById('idPreviewNumber').textContent}`,
        `Term: ${document.getElementById('idPreviewTerm').textContent}`,
    ].join('\n');
    const blob = new Blob([text], { type: 'text/plain' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${document.getElementById('idPreviewNumber').textContent || 'official-id'}.txt`;
    link.click();
    URL.revokeObjectURL(link.href);
}

function formatDate(value) {
    if (!value) return 'Not set';
    return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: '2-digit' });
}

document.getElementById('officialForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('officialId').value;
    const formData = new FormData(event.currentTarget);
    const payload = Object.fromEntries(formData);
    payload.is_active = document.querySelector('[name="is_active"]').checked ? 1 : 0;

    const res = await fetch(id ? `/officials/${id}` : '/officials', {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    });

    if (res.ok) {
        officialModal.hide();
        location.reload();
        return;
    }

    const error = await res.json();
    showAlert(error.message || 'Unable to save official profile.', 'danger');
});

document.getElementById('designationForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = Object.fromEntries(new FormData(event.currentTarget));
    const res = await fetch('{{ route('officials.assignDesignation') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    });

    if (res.ok) {
        designationModal.hide();
        location.reload();
        return;
    }

    const error = await res.json();
    showAlert(error.message || 'Unable to assign designation.', 'danger');
});
</script>
@endsection
