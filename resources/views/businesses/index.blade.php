@extends("layouts.app")

@section("title", "Business Permits - Barangay Management System")
@section("page-title", "Business Permit Management")

@section("styles")
<style>
    .business-permit-page .si-blue { background: #eff6ff; color: #1a56db; }
    .business-permit-page .si-green { background: #f0fdf4; color: #16a34a; }
    .business-permit-page .si-amber { background: #fffbeb; color: #d97706; }
    .business-permit-page .si-red { background: #fef2f2; color: #dc2626; }
    .business-permit-page .badge-active { background: #dcfce7; color: #15803d; }
    .business-permit-page .badge-expiring { background: #fef3c7; color: #b45309; }
    .business-permit-page .badge-expired { background: #fee2e2; color: #dc2626; }
    .business-permit-page .badge-pending { background: #e2e8f0; color: #475569; }
    .business-permit-page .form-control,
    .business-permit-page .form-select { border-radius: 8px; font-size: 13px; }
    .business-permit-page .form-label { font-size: 12.5px; font-weight: 700; color: #0f172a; }
    .owner-search-results {
        position: absolute; z-index: 1060; width: 100%; max-height: 190px; overflow-y: auto;
        background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; margin-top: 4px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
    }
    .owner-search-results button {
        display: block; width: 100%; padding: 8px 10px; border: 0; background: #fff;
        text-align: left; font-size: 12.5px; color: #0f172a;
    }
    .owner-search-results button:hover { background: #eff6ff; }
    .qr-placeholder {
        width: 104px; height: 104px; background: #f8fafc; border: 2px dashed #cbd5e1;
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
        flex-direction: column; gap: 4px; font-size: 11px; color: #94a3b8; font-weight: 700;
    }
    .qr-placeholder.ready { background: #eff6ff; border-color: #93c5fd; color: #1a56db; }
    .chart-wrap { position: relative; height: 220px; }
    .alert-row {
        display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px;
        background: #fffbeb; border: 1px solid #fde68a; margin-bottom: 10px;
    }
    .alert-row .ar-icon { color: #d97706; font-size: 18px; flex-shrink: 0; }
    .alert-row .ar-biz { font-size: 13px; font-weight: 800; color: #0f172a; }
    .alert-row .ar-sub { font-size: 11.5px; color: #92400e; }
    .alert-row .ar-days { font-size: 12px; font-weight: 800; color: #d97706; margin-left: auto; white-space: nowrap; }
    .permit-table .permit-no { font-family: 'DM Mono', monospace; font-size: 11.5px; color: #64748b; }
    .permit-table .business-name { font-weight: 800; color: #0f172a; }
    .permit-action {
        border: none; background: #f8fafc; color: #0f172a; width: 32px; height: 32px;
        border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;
    }
    .permit-action:hover { background: #e2e8f0; }
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length { display: none; }
    .dataTables_wrapper .dataTables_info { padding: 12px 20px; font-size: 13px; color: #64748b; }
    .dataTables_wrapper .dataTables_paginate { padding: 10px 20px; }
</style>
@endsection

@section("content")
<div class="business-permit-page">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-800 mb-1" style="font-size:18px;">Business Permit Management</h5>
            <p class="mb-0" style="font-size:13px;color:#64748b;">Register, track, and renew barangay business clearances and permits.</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
                style="border-radius:8px;font-size:13.5px;font-weight:700;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Register New Business
        </button>
    </div>

    <div id="businessAlert" class="mb-3"></div>

    <div class="section-heading">Overview</div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon si-blue"><i class="bi bi-shop-window"></i></div>
                <div><div class="stat-value">{{ $permitSummary['registered'] }}</div><div class="stat-label">Registered Businesses</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon si-green"><i class="bi bi-patch-check-fill"></i></div>
                <div><div class="stat-value">{{ $permitSummary['active'] }}</div><div class="stat-label">Active Permits</div><span class="stat-badge badge-active">{{ $permitSummary['active_rate'] }}%</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon si-amber"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div><div class="stat-value">{{ $permitSummary['expiring'] }}</div><div class="stat-label">Expiring in 30 days</div><span class="stat-badge badge-expiring">Alert</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon si-red"><i class="bi bi-x-circle-fill"></i></div>
                <div><div class="stat-value">{{ $permitSummary['expired'] }}</div><div class="stat-label">Expired / Lapsed</div><span class="stat-badge badge-expired">Renewal needed</span></div>
            </div>
        </div>
    </div>

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
                <div class="card-heading" style="color:#b45309;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Expiring Soon</div>
                <div class="card-sub mb-3">Permits expiring within 30 days</div>
                @forelse ($expiringSoon as $item)
                    <div class="alert-row">
                        <i class="bi bi-shop ar-icon"></i>
                        <div>
                            <div class="ar-biz">{{ $item['business_name'] }}</div>
                            <div class="ar-sub">{{ $item['business_type'] }}</div>
                        </div>
                        <div class="ar-days">{{ $item['days'] }} days</div>
                    </div>
                @empty
                    <p class="text-muted mb-0" style="font-size:13px;">No permits are expiring in the next 30 days.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="section-heading">Issue Permit</div>
            <div class="chart-card">
                <div class="card-heading mb-1"><i class="bi bi-qr-code me-2 text-primary"></i>Issue Business Clearance</div>
                <div class="card-sub mb-3">Create a new clearance with generated reference number</div>
                <form id="quickIssueForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="quickBusinessSelect">Business Name</label>
                        <select class="form-select" id="quickBusinessSelect" required>
                            <option value="">Search registered business...</option>
                            @foreach ($businessOptions as $option)
                                <option value="{{ $option['id'] }}"
                                        data-permit="{{ $option['permit_number'] }}"
                                        data-can-issue="{{ $option['can_issue'] ? '1' : '0' }}"
                                        data-name="{{ $option['business_name'] }}">
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label" for="permitYear">Permit Year</label>
                            <select class="form-select" id="permitYear">
                                <option value="{{ $permitYear }}">{{ $permitYear }}</option>
                                <option value="{{ $permitYear + 1 }}">{{ $permitYear + 1 }}</option>
                                <option value="{{ $permitYear - 1 }}">{{ $permitYear - 1 }}</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="quickIssuedDate">Issue Date</label>
                            <input type="date" class="form-control" id="quickIssuedDate" name="issued_date" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <input type="hidden" id="quickExpiryDate" name="expiry_date">
                    <div class="mb-4 d-flex align-items-center gap-3">
                        <div class="qr-placeholder" id="qrPreview">
                            <i class="bi bi-qr-code" style="font-size:32px;color:#cbd5e1;"></i>
                            <span>QR Preview</span>
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">Reference No.</div>
                            <div id="referencePreview" style="font-size:15px;font-weight:800;font-family:'DM Mono',monospace;color:#0f172a;">BP-{{ $permitYear }}-?????</div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Generated upon issuance</div>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius:8px;font-size:13.5px;font-weight:700;padding:10px;">
                        <i class="bi bi-file-earmark-check-fill"></i> Issue Clearance
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="section-heading">Renewal Tracker</div>
            <div class="table-card">
                <div class="table-header">
                    <span class="heading">Business Permits</span>
                    <div class="input-group" style="max-width:240px;">
                        <input type="text" class="form-control" id="permitSearch" placeholder="Search..." style="font-size:12.5px;border-radius:8px 0 0 8px;">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton" style="border-radius:0 8px 8px 0;padding:0 10px;"><i class="bi bi-search" style="font-size:12px;"></i></button>
                    </div>
                    <select class="form-select" id="statusFilter" style="width:auto;font-size:12.5px;border-radius:8px;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="expiring">Expiring</option>
                        <option value="expired">Expired</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 permit-table" id="businessesTable">
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
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="businessModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Business Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="businessForm">
                @csrf
                <input type="hidden" id="businessId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Business Name</label>
                            <input type="text" class="form-control" name="business_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            @php($types = ['Supermarket', 'Laundry Service', 'Pharmacy', 'Restaurant', 'Retail Service', 'Sari-Sari', 'Food Service', 'Health', 'Tailoring', 'Workshop', 'Transport', 'Other'])
                            <label class="form-label fw-600">Business Type</label>
                            <select name="business_type" class="form-select" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Address</label>
                            <textarea class="form-control" name="business_address" rows="2" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Date Established</label>
                            <input type="date" class="form-control" name="date_established" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Closed">Closed</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-600 mb-0">Business Owners</label>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addOwnerRow()"><i class="bi bi-plus-lg"></i> Add Owner</button>
                        </div>
                        <div id="ownersContainer"></div>
                        <div class="invalid-feedback d-block" id="ownersError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Business</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="renewPermitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Renew Business Permit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="renewPermitForm">
                @csrf
                <input type="hidden" name="business_id" id="renewBusinessId">
                <input type="hidden" name="permit_id" id="renewPermitId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Renewal Date</label>
                        <input type="date" class="form-control" name="renewal_date" id="renewalDate">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">New Expiry Date</label>
                        <input type="date" class="form-control" name="new_expiry_date" id="renewalExpiryDate">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Fee Paid</label>
                        <input type="number" class="form-control" name="fee_paid" min="0" step="0.01" value="500.00" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-clockwise"></i> Renew Permit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="permitHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800" id="permitHistoryTitle">Permit History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="permitHistoryBody"></div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const businessBaseUrl = @json(url('/businesses'));
    const routes = {
        data: @json(route('businesses.data')),
        store: @json(route('businesses.store')),
        residentSearch: @json(route('businesses.residents.search')),
    };
    const permitStatusLabels = ['Active', 'Expiring', 'Expired', 'Pending'];
    const permitStatusCounts = [
        @json($permitSummary['active']),
        @json($permitSummary['expiring']),
        @json($permitSummary['expired']),
        @json($permitSummary['pending']),
    ];
    const typeLabels = @json($typeDistribution->keys()->values());
    const typeCounts = @json($typeDistribution->values());

    if (csrfToken && window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        window.axios.defaults.headers.common.Accept = 'application/json';
    }

    const businessModal = new bootstrap.Modal(document.getElementById('businessModal'));
    const renewPermitModal = new bootstrap.Modal(document.getElementById('renewPermitModal'));
    const permitHistoryModal = new bootstrap.Modal(document.getElementById('permitHistoryModal'));
    const businessForm = document.getElementById('businessForm');
    const ownersContainer = document.getElementById('ownersContainer');
    const alertBox = document.getElementById('businessAlert');
    let businessesTable;

    const today = () => new Date().toISOString().slice(0, 10);

    const annualExpiry = (dateValue) => {
        const date = dateValue ? new Date(`${dateValue}T00:00:00`) : new Date();
        date.setFullYear(date.getFullYear() + 1);
        date.setDate(date.getDate() - 1);
        return date.toISOString().slice(0, 10);
    };

    const showAlert = (message, type = 'success') => {
        alertBox.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    };

    const clearValidation = (form) => {
        form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach((feedback) => {
            feedback.textContent = '';
        });
        document.getElementById('ownersError').textContent = '';
    };

    const showValidation = (form, errors = {}) => {
        Object.entries(errors).forEach(([field, messages]) => {
            const normalized = field.replace(/\.\d+$/, '');
            const input = form.querySelector(`[name="${normalized}"], [name="${normalized}[]"]`);

            if (normalized.startsWith('business_owner_resident_ids') || normalized.startsWith('ownership_percentages')) {
                document.getElementById('ownersError').textContent = messages[0] || 'Please review the owner rows.';
                return;
            }

            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.closest('.col-md-6, .col-12, .mb-3, .modal-body')
                    ?.querySelector('.invalid-feedback');

                if (feedback) {
                    feedback.textContent = messages[0] || 'Invalid value.';
                }
            }
        });
    };

    const escapeHtml = (value = '') => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const searchResidents = async (query) => {
        const url = new URL(routes.residentSearch, window.location.origin);
        url.searchParams.set('q', query);
        const response = await axios.get(url.toString());
        return response.data.results || [];
    };

    const renderOwnerResults = (row, residents) => {
        const results = row.querySelector('.owner-search-results');
        results.innerHTML = residents.length
            ? residents.map((resident) => `
                <button type="button"
                        data-resident-id="${escapeHtml(resident.id)}"
                        data-resident-text="${escapeHtml(resident.text)}"
                        data-resident-name="${escapeHtml(resident.name)}">
                    ${escapeHtml(resident.text)}
                </button>
            `).join('')
            : '<button type="button" disabled>No matching residents found</button>';
        results.classList.remove('d-none');
    };

    const attachOwnerSearch = (row) => {
        const input = row.querySelector('.owner-search-input');
        const hidden = row.querySelector('[name="business_owner_resident_ids[]"]');
        const results = row.querySelector('.owner-search-results');

        input.addEventListener('input', () => {
            hidden.value = '';
            clearTimeout(input.searchTimer);
            const query = input.value.trim();

            if (query.length < 2) {
                results.classList.add('d-none');
                return;
            }

            input.searchTimer = setTimeout(async () => {
                try {
                    renderOwnerResults(row, await searchResidents(query));
                } catch (error) {
                    results.innerHTML = '<button type="button" disabled>Unable to search residents</button>';
                    results.classList.remove('d-none');
                }
            }, 250);
        });

        results.addEventListener('click', (event) => {
            const option = event.target.closest('button[data-resident-id]');
            if (!option) {
                return;
            }

            hidden.value = option.dataset.residentId;
            input.value = option.dataset.residentText || option.dataset.residentName || '';
            input.classList.remove('is-invalid');
            results.classList.add('d-none');
        });
    };

    window.addOwnerRow = (selectedResidentId = '', role = 'Owner', percentage = '', selectedOwnerText = '') => {
        const row = document.createElement('div');
        row.className = 'owner-row row g-2 align-items-end mb-2';
        row.innerHTML = `
            <div class="col-md-5 position-relative">
                <input type="hidden" name="business_owner_resident_ids[]" value="${escapeHtml(selectedResidentId)}">
                <input type="text" class="form-control owner-search-input" value="${escapeHtml(selectedOwnerText)}" placeholder="Search resident name or number" autocomplete="off" required>
                <div class="owner-search-results d-none"></div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="ownership_roles[]" required>
                    ${['Owner', 'Co-owner', 'Representative'].map((item) => `<option value="${item}" ${item === role ? 'selected' : ''}>${item}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="ownership_percentages[]" min="0" max="100" step="0.01" value="${percentage ?? ''}" placeholder="%">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100" title="Remove owner">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        row.querySelector('button').addEventListener('click', () => row.remove());
        ownersContainer.appendChild(row);
        attachOwnerSearch(row);
    };

    window.openCreateModal = () => {
        businessForm.reset();
        clearValidation(businessForm);
        document.getElementById('businessId').value = '';
        ownersContainer.innerHTML = '';
        addOwnerRow();
        document.querySelector('#businessModal .modal-title').textContent = 'Register New Business';
        businessModal.show();
    };

    const fillBusinessForm = (payload) => {
        const business = payload.data;
        businessForm.business_name.value = business.business_name || '';
        businessForm.business_type.value = business.business_type || 'Other';
        businessForm.business_address.value = business.business_address || '';
        businessForm.date_established.value = business.date_established || '';
        businessForm.status.value = business.status || 'Active';
        document.getElementById('businessId').value = business.id;
        ownersContainer.innerHTML = '';

        (business.owners || []).forEach((owner) => {
            addOwnerRow(owner.resident_id || '', owner.ownership_role || 'Owner', owner.ownership_percentage || '', owner.name || '');
        });

        if (!ownersContainer.children.length) {
            addOwnerRow();
        }
    };

    const reloadTable = (resetPaging = false) => {
        businessesTable?.ajax.reload(null, resetPaging);
    };

    const upsertBusinessOption = (option) => {
        if (!option?.id) {
            return;
        }

        const select = document.getElementById('quickBusinessSelect');
        let node = Array.from(select.options).find((item) => item.value === String(option.id));

        if (!node) {
            node = document.createElement('option');
            node.value = option.id;
            select.appendChild(node);
        }

        node.textContent = option.label;
        node.dataset.permit = option.permit_number || '';
        node.dataset.canIssue = option.can_issue ? '1' : '0';
        node.dataset.name = option.business_name || '';
    };

    const initCharts = () => {
        const statusCanvas = document.getElementById('permitStatusChart');
        const typeCanvas = document.getElementById('bizTypeChart');

        if (statusCanvas && window.Chart) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: permitStatusLabels,
                    datasets: [{
                        data: permitStatusCounts,
                        backgroundColor: ['#16a34a', '#f59e0b', '#dc2626', '#94a3b8'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    cutout: '64%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                    maintainAspectRatio: false,
                },
            });
        }

        if (typeCanvas && window.Chart) {
            new Chart(typeCanvas, {
                type: 'bar',
                data: {
                    labels: typeLabels,
                    datasets: [{ data: typeCounts, backgroundColor: '#1a56db', borderRadius: 6 }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                    },
                    maintainAspectRatio: false,
                },
            });
        }
    };

    const initTable = () => {
        businessesTable = $('#businessesTable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            ajax: {
                url: routes.data,
                data: (data) => {
                    data.search = data.search || {};
                    data.search.value = document.getElementById('permitSearch').value;
                    data.search.regex = false;
                    data.status_filter = document.getElementById('statusFilter').value;
                },
            },
            order: [[1, 'asc']],
            columns: [
                { data: 'permit_number', name: 'permit_number', className: 'permit-no', orderable: false, searchable: false },
                {
                    data: 'business_name',
                    name: 'business_name',
                    searchable: false,
                    render: (data) => `<span class="business-name">${data}</span>`,
                },
                { data: 'owner_plain', name: 'owner_plain', orderable: false, searchable: false },
                { data: 'business_type', name: 'business_type', searchable: false },
                { data: 'issued_date', name: 'issued_date', orderable: false, searchable: false },
                { data: 'expiry_date', name: 'expiry_date', orderable: false, searchable: false },
                { data: 'permit_status_badge', name: 'permit_status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            language: {
                processing: 'Loading business permits...',
                emptyTable: 'No businesses registered yet.',
                zeroRecords: 'No matching businesses found.',
            },
        });
    };

    const loadBusinessForEdit = async (businessId) => {
        clearValidation(businessForm);
        const response = await axios.get(`${businessBaseUrl}/${businessId}/edit`);
        fillBusinessForm(response.data);
        document.querySelector('#businessModal .modal-title').textContent = 'Edit Business';
        businessModal.show();
    };

    const issuePermit = async (businessId, issuedDate, expiryDate) => {
        const response = await axios.post(`${businessBaseUrl}/${businessId}/permits`, {
            issued_date: issuedDate,
            expiry_date: expiryDate,
        });
        upsertBusinessOption(response.data.business_option);
        showAlert(response.data.message || 'Business permit issued successfully.');
        reloadTable();
    };

    const openRenewModal = (businessId, permitId) => {
        if (!permitId) {
            showAlert('Please issue a permit before renewing.', 'warning');
            return;
        }

        document.getElementById('renewBusinessId').value = businessId;
        document.getElementById('renewPermitId').value = permitId;
        document.getElementById('renewalDate').value = today();
        document.getElementById('renewalExpiryDate').value = annualExpiry(today());
        clearValidation(document.getElementById('renewPermitForm'));
        renewPermitModal.show();
    };

    const openPermitHistory = async (businessId) => {
        const response = await axios.get(`${businessBaseUrl}/${businessId}/permits/history`);
        const { business, permits } = response.data;
        document.getElementById('permitHistoryTitle').textContent = `${business.business_name} Permit History`;
        document.getElementById('permitHistoryBody').innerHTML = permits.length
            ? `
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Permit No.</th>
                                <th>Issued</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Renewals</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${permits.map((permit) => `
                                <tr>
                                    <td class="permit-no">${permit.permit_number}</td>
                                    <td>${permit.issued_date || '-'}</td>
                                    <td>${permit.expiry_date || '-'}</td>
                                    <td>${permit.display_status}</td>
                                    <td>${permit.renewals.length}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `
            : '<p class="text-muted mb-0">No permits have been issued for this business yet.</p>';
        permitHistoryModal.show();
    };

    document.getElementById('statusFilter').addEventListener('change', () => reloadTable(true));
    document.getElementById('searchButton').addEventListener('click', () => {
        reloadTable(true);
    });
    document.getElementById('permitSearch').addEventListener('input', (event) => {
        clearTimeout(event.target.searchTimer);
        event.target.searchTimer = setTimeout(() => {
            reloadTable(true);
        }, 300);
    });

    document.getElementById('quickIssuedDate').addEventListener('change', (event) => {
        document.getElementById('quickExpiryDate').value = annualExpiry(event.target.value);
    });
    document.getElementById('quickExpiryDate').value = annualExpiry(document.getElementById('quickIssuedDate').value);

    document.getElementById('quickBusinessSelect').addEventListener('change', (event) => {
        const selected = event.target.selectedOptions[0];
        const canIssue = selected?.dataset.canIssue === '1';
        document.getElementById('qrPreview').classList.toggle('ready', Boolean(event.target.value && canIssue));
        document.getElementById('referencePreview').textContent = `BP-${document.getElementById('permitYear').value}-?????`;
    });

    document.getElementById('quickIssueForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const businessSelect = document.getElementById('quickBusinessSelect');
        const selected = businessSelect.selectedOptions[0];

        if (!businessSelect.value) {
            showAlert('Please select a business first.', 'warning');
            return;
        }

        if (selected?.dataset.canIssue !== '1') {
            showAlert('This business already has an active permit.', 'warning');
            return;
        }

        try {
            await issuePermit(
                businessSelect.value,
                document.getElementById('quickIssuedDate').value,
                document.getElementById('quickExpiryDate').value
            );
            businessSelect.value = '';
            document.getElementById('qrPreview').classList.remove('ready');
        } catch (error) {
            showAlert(error.response?.data?.message || 'Unable to issue permit.', 'danger');
        }
    });

    businessForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearValidation(businessForm);

        const businessId = document.getElementById('businessId').value;
        const formData = new FormData(businessForm);
        if (businessId) {
            formData.append('_method', 'PUT');
        }

        try {
            const response = businessId
                ? await axios.post(`${businessBaseUrl}/${businessId}`, formData)
                : await axios.post(routes.store, formData);

            businessModal.hide();
            upsertBusinessOption(response.data.business_option);
            showAlert(response.data.message || 'Business saved successfully.');
            reloadTable();
        } catch (error) {
            if (error.response?.status === 422) {
                showValidation(businessForm, error.response.data.errors || {});
                return;
            }
            showAlert(error.response?.data?.message || 'Failed to save business.', 'danger');
        }
    });

    document.getElementById('renewalDate').addEventListener('change', (event) => {
        document.getElementById('renewalExpiryDate').value = annualExpiry(event.target.value);
    });

    document.getElementById('renewPermitForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.target;
        clearValidation(form);

        try {
            const businessId = document.getElementById('renewBusinessId').value;
            const permitId = document.getElementById('renewPermitId').value;
            const response = await axios.post(`${businessBaseUrl}/${businessId}/permits/${permitId}/renew`, new FormData(form));

            renewPermitModal.hide();
            upsertBusinessOption(response.data.business_option);
            showAlert(response.data.message || 'Business permit renewed successfully.');
            reloadTable();
        } catch (error) {
            if (error.response?.status === 422) {
                showValidation(form, error.response.data.errors || {});
                return;
            }
            showAlert(error.response?.data?.message || 'Unable to renew permit.', 'danger');
        }
    });

    document.getElementById('businessesTable').addEventListener('click', async (event) => {
        const button = event.target.closest('[data-business-action]');
        if (!button) {
            return;
        }

        const businessId = button.dataset.businessId;

        try {
            switch (button.dataset.businessAction) {
                case 'edit':
                    await loadBusinessForEdit(businessId);
                    break;
                case 'renew':
                    openRenewModal(businessId, button.dataset.permitId);
                    break;
                case 'history':
                    await openPermitHistory(businessId);
                    break;
                case 'delete':
                    if (confirm('Delete this business permit row?')) {
                        const response = await axios.delete(`${businessBaseUrl}/${businessId}`);
                        const deletedId = String(response.data.business_id || businessId);
                        Array.from(document.getElementById('quickBusinessSelect').options)
                            .find((item) => item.value === deletedId)
                            ?.remove();
                        showAlert(response.data.message || 'Business deleted successfully.');
                        reloadTable();
                    }
                    break;
            }
        } catch (error) {
            showAlert(error.response?.data?.message || 'Action failed. Please try again.', 'danger');
        }
    });

    initCharts();
    initTable();
});
</script>
@endsection
