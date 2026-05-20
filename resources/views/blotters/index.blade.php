@extends("layouts.app")

@section("title", "Blotter Management – Barangay Management System")
@section("page-title", "Blotter Management")

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Blotter Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Log, track, and resolve barangay complaints and incidents.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()" 
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-plus-lg"></i> Add Blotter Record
    </button>
</div>

<div id="alertContainer"></div>

<div class="table-card">
    <table id="blottersTable" class="table table-striped">
        <thead>
            <tr>
                <th>Case No.</th>
                <th>Complainant</th>
                <th>Respondent</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal fade" id="blotterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:12px;border:1px solid #e2e8f0;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800">Blotter Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="blotterForm" style="display:none;">
                @csrf
                <input type="hidden" id="blotterId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Case Number</label>
                        <input type="text" class="form-control" name="case_number" placeholder="e.g., BLT-2025-0048" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Complainant</label>
                            <input type="text" class="form-control" name="complainant" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Respondent</label>
                            <input type="text" class="form-control" name="respondent" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Description</label>
                        <textarea class="form-control" name="incident_description" rows="3" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Incident Date</label>
                            <input type="datetime-local" class="form-control" name="incident_date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Select Status</option>
                                <option value="open">Open</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="resolved">Resolved</option>
                                <option value="referred">Referred</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="blotterViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:12px;border:1px solid #e2e8f0;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800">View Blotter Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewContent"></div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editBtn" onclick="editCurrentBlotter()">Edit</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push("scripts")
<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.2/datatables.min.css">
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.2/datatables.min.js"></script>
<script>
const blotterModal = new bootstrap.Modal(document.getElementById('blotterModal'));
const blotterViewModal = new bootstrap.Modal(document.getElementById('blotterViewModal'));
const blotterForm = document.getElementById('blotterForm');
const viewContent = document.getElementById('viewContent');
const alertContainer = document.getElementById('alertContainer');
let currentBlotterId = null;
let blottersTable = null;

const axiosInstance = window.axios;
if (axiosInstance) {
    axiosInstance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axiosInstance.defaults.headers.common['Accept'] = 'application/json';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        axiosInstance.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }
}

function showAlert(message, type = 'success') {
    if (!alertContainer) return;
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function clearValidation() {
    blotterForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    blotterForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
}

function showFormErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = blotterForm.querySelector(`[name="${field}"]`);
        if (!input) return;
        input.classList.add('is-invalid');
        const feedback = input.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = errors[field][0];
        }
    });
}

function toLocalDateTime(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    return date.toISOString().slice(0, 16);
}

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function openCreateModal() {
    blotterForm.reset();
    clearValidation();
    document.getElementById('blotterId').value = '';
    document.querySelector('#blotterModal .modal-title').textContent = 'Add Blotter Record';
    blotterForm.style.display = 'block';
    blotterModal.show();
}

async function editBlotter(id) {
    if (!axiosInstance) return;
    currentBlotterId = id;
    clearValidation();
    try {
        const response = await axiosInstance.get(`/blotters/${id}/edit`);
        const record = response.data?.data || response.data;
        document.getElementById('blotterId').value = record.id;
        document.querySelector('[name="case_number"]').value = record.case_number || '';
        document.querySelector('[name="complainant"]').value = record.complainant || '';
        document.querySelector('[name="respondent"]').value = record.respondent || '';
        document.querySelector('[name="incident_description"]').value = record.incident_description || '';
        document.querySelector('[name="incident_date"]').value = toLocalDateTime(record.incident_date);
        document.querySelector('[name="status"]').value = record.status || '';
        document.querySelector('#blotterModal .modal-title').textContent = 'Edit Blotter Record';
        blotterForm.style.display = 'block';
        blotterModal.show();
    } catch (error) {
        showAlert('Failed to load blotter record.', 'danger');
    }
}

async function viewBlotter(id) {
    if (!axiosInstance) return;
    currentBlotterId = id;
    try {
        const response = await axiosInstance.get(`/blotters/${id}`);
        const record = response.data?.data || response.data;
        const filedBy = record.filed_by?.name || '—';
        const incidentDate = record.incident_date ? new Date(record.incident_date).toLocaleString() : '—';
        const statusLabel = record.status ? record.status.charAt(0).toUpperCase() + record.status.slice(1) : '—';

        viewContent.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fw-600">Case No.</div>
                    <div>${escapeHtml(record.case_number || '—')}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600">Status</div>
                    <div>${escapeHtml(statusLabel)}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600">Complainant</div>
                    <div>${escapeHtml(record.complainant || '—')}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600">Respondent</div>
                    <div>${escapeHtml(record.respondent || '—')}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600">Incident Date</div>
                    <div>${escapeHtml(incidentDate)}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600">Filed By</div>
                    <div>${escapeHtml(filedBy)}</div>
                </div>
            </div>
            <div class="mt-3">
                <div class="fw-600">Description</div>
                <div>${escapeHtml(record.incident_description || '—')}</div>
            </div>
        `;
        blotterViewModal.show();
    } catch (error) {
        showAlert('Failed to load blotter details.', 'danger');
    }
}

function editCurrentBlotter() {
    if (currentBlotterId) {
        blotterViewModal.hide();
        editBlotter(currentBlotterId);
    }
}

async function deleteBlotter(id) {
    if (!axiosInstance) return;
    if (!confirm('Are you sure you want to delete this blotter record?')) return;
    try {
        const response = await axiosInstance.delete(`/blotters/${id}`);
        showAlert(response.data?.message || 'Blotter record deleted successfully');
        if (blottersTable) {
            blottersTable.ajax.reload(null, false);
        }
    } catch (error) {
        showAlert(error.response?.data?.message || 'Failed to delete record.', 'danger');
    }
}

blotterForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!axiosInstance) return;
    clearValidation();

    const id = document.getElementById('blotterId').value;
    const url = id ? `/blotters/${id}` : '/blotters';
    const method = id ? 'put' : 'post';
    const formData = new FormData(blotterForm);
    const data = Object.fromEntries(formData);

    try {
        const response = await axiosInstance({ method, url, data });
        showAlert(response.data?.message || 'Blotter record saved successfully');
        blotterModal.hide();
        blotterForm.reset();
        if (blottersTable) {
            blottersTable.ajax.reload(null, false);
        }
    } catch (error) {
        if (error.response?.status === 422) {
            showFormErrors(error.response.data.errors || {});
            return;
        }
        showAlert(error.response?.data?.message || 'An error occurred while saving.', 'danger');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (window.$ && $.fn.DataTable) {
        blottersTable = $("#blottersTable").DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route("blotters.index") }}",
                dataSrc: "data",
                headers: { 'Accept': 'application/json' }
            },
            columns: [
                { data: "case_number" },
                { data: "complainant" },
                { data: "respondent" },
                { data: "incident_date", render: function(data) {
                    return data ? new Date(data).toLocaleDateString() : '—';
                }},
                { data: "status", render: function(data) {
                    const styles = {
                        open: { bg: "#fee2e2", text: "#dc2626" },
                        ongoing: { bg: "#fef3c7", text: "#b45309" },
                        resolved: { bg: "#dcfce7", text: "#15803d" },
                        referred: { bg: "#f1f5f9", text: "#64748b" }
                    };
                    const style = styles[data] || { bg: "#f1f5f9", text: "#64748b" };
                    const label = data ? data.charAt(0).toUpperCase() + data.slice(1) : '—';
                    return `<span class="badge" style="background:${style.bg};color:${style.text};">${label}</span>`;
                }},
                { data: "id", render: function(data) {
                    return `<button class="btn btn-sm btn-light" onclick="viewBlotter(${data})"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-light" onclick="editBlotter(${data})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light" onclick="deleteBlotter(${data})"><i class="bi bi-trash"></i></button>`;
                }, orderable: false, searchable: false }
            ]
        });
    }
});
</script>
@endpush
