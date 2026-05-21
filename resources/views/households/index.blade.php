@extends('layouts.app')

@section('title', 'Households - Barangay Management System')
@section('page-title', 'Households & Purok')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .select2-container .select2-selection--single { height:38px; border:1px solid #cbd5e1; border-radius:8px; display:flex; align-items:center; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px; padding-left:12px; color:#1e293b; font-size:13px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
    .select2-container--open { z-index:1065; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Households</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Organize residents by household, Purok, voters, and family size.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('puroks.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-map"></i> Puroks
        </a>
        <a href="{{ route('households.statistics') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-graph-up"></i> Statistics
        </a>
        @can('households.manage')
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-household-action="create" style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
                <i class="bi bi-plus-lg"></i> Add Household
            </button>
        @endcan
    </div>
</div>

<div id="householdAlert" class="mb-3"></div>

<div class="table-card">
    <table class="table table-hover" id="householdsTable">
        <thead>
            <tr>
                <th>Purok</th>
                <th>Address</th>
                <th>Head</th>
                <th>Family Size</th>
                <th>Registered Voters</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="householdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800" id="householdModalTitle">Household Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="householdForm">
                @csrf
                <input type="hidden" id="householdId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Purok</label>
                        <select name="purok_id" id="purokSelect" class="form-select" required>
                            <option value="">Select Purok</option>
                            @foreach($puroks as $purok)
                                <option value="{{ $purok->id }}">{{ $purok->purok_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">House Number</label>
                        <input type="text" class="form-control" name="house_number" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Street</label>
                        <input type="text" class="form-control" name="street" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3 d-none" id="headResidentWrap">
                        <label class="form-label fw-600">Household Head</label>
                        <select name="head_resident_id" id="headResidentSelect" class="form-select">
                            <option value="">Unassigned</option>
                        </select>
                        <small class="text-muted">Available after residents are assigned to this household.</small>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Household</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const householdModal = new bootstrap.Modal(document.getElementById('householdModal'));
const householdForm = document.getElementById('householdForm');
const alertContainer = document.getElementById('householdAlert');
let householdsTable = null;

function httpClient() {
    if (!window.axios) {
        showAlert('The page is still loading. Please try again in a moment.', 'warning');
        return null;
    }

    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    window.axios.defaults.headers.common['Accept'] = 'application/json';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    return window.axios;
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
    householdForm.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    householdForm.querySelectorAll('.invalid-feedback').forEach((el) => { el.textContent = ''; });
}

function showFormErrors(errors) {
    Object.keys(errors).forEach((field) => {
        const input = householdForm.querySelector(`[name="${field}"]`);
        if (!input) return;
        input.classList.add('is-invalid');
        const feedback = input.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = errors[field][0];
        }
    });
}

function resetHeadResidentOptions(residents = [], selected = '') {
    const select = document.getElementById('headResidentSelect');
    select.innerHTML = '<option value="">Unassigned</option>';
    residents.forEach((resident) => {
        const option = new Option(resident.name, resident.id, false, resident.id === selected);
        select.appendChild(option);
    });
    if (window.$ && $.fn.select2) {
        $('#headResidentSelect').trigger('change.select2');
    }
}

function initSelect2() {
    if (!window.$ || typeof $.fn.select2 !== 'function') return;
    $('#purokSelect, #headResidentSelect').select2({
        dropdownParent: $('#householdModal'),
        width: '100%'
    });
}

function openCreateModal() {
    if (!document.querySelector('[data-household-action="create"]')) return;
    householdForm.reset();
    clearValidation();
    document.getElementById('householdId').value = '';
    document.getElementById('householdModalTitle').textContent = 'Add Household';
    document.getElementById('headResidentWrap').classList.add('d-none');
    resetHeadResidentOptions();
    $('#purokSelect').val('').trigger('change.select2');
    householdModal.show();
}

async function openEditModal(id) {
    clearValidation();
    const client = httpClient();
    if (!client) return;

    try {
        const response = await client.get(`/households/${id}/edit`);
        const data = response.data;
        document.getElementById('headResidentWrap').classList.remove('d-none');
        document.getElementById('householdId').value = data.id;
        document.getElementById('householdModalTitle').textContent = 'Edit Household';
        householdForm.querySelector('[name="house_number"]').value = data.house_number || '';
        householdForm.querySelector('[name="street"]').value = data.street || '';
        $('#purokSelect').val(String(data.purok_id)).trigger('change.select2');
        resetHeadResidentOptions(data.residents || [], data.head_resident_id || '');
        householdModal.show();
    } catch (error) {
        showAlert(error.response?.data?.message || 'Failed to load household details.', 'danger');
    }
}

async function deleteHousehold(id) {
    if (!confirm('Delete this household? Residents must be reassigned first.')) return;
    const client = httpClient();
    if (!client) return;

    try {
        const response = await client.delete(`/households/${id}`);
        showAlert(response.data?.message || 'Household deleted successfully.');
        householdsTable?.ajax.reload(null, false);
    } catch (error) {
        showAlert(error.response?.data?.message || 'Failed to delete household.', 'danger');
    }
}

householdForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearValidation();

    const id = document.getElementById('householdId').value;
    const url = id ? `/households/${id}` : '/households';
    const method = id ? 'put' : 'post';
    const data = Object.fromEntries(new FormData(householdForm));
    const client = httpClient();
    if (!client) return;

    if (!id) {
        delete data.head_resident_id;
    }

    try {
        const response = await client({ method, url, data });
        showAlert(response.data?.message || 'Household saved successfully.');
        householdModal.hide();
        householdForm.reset();
        householdsTable?.ajax.reload(null, false);
    } catch (error) {
        if (error.response?.status === 422) {
            showFormErrors(error.response.data.errors || {});
            return;
        }
        showAlert(error.response?.data?.message || 'An error occurred while saving.', 'danger');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    initSelect2();

    if (window.$ && $.fn.DataTable) {
        householdsTable = $('#householdsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('households.data') }}",
                dataSrc: 'data',
                headers: { Accept: 'application/json' }
            },
            columns: [
                { data: 'purok_name', name: 'purok.purok_name', orderable: false, searchable: false },
                { data: 'address', name: 'house_number' },
                { data: 'head_name', name: 'head_name', orderable: false, searchable: false },
                { data: 'family_size', name: 'family_size', searchable: false },
                { data: 'registered_voters', name: 'registered_voters', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[1, 'asc']],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            language: {
                search: 'Search households:',
                info: 'Showing _START_ to _END_ of _TOTAL_ households',
                infoEmpty: 'No households available',
                infoFiltered: '(filtered from _MAX_ total households)'
            }
        });
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-household-action]');
        if (!button) return;

        const { householdAction, householdId } = button.dataset;

        if (householdAction === 'create') {
            openCreateModal();
            return;
        }

        if (householdAction === 'edit') {
            openEditModal(householdId);
            return;
        }

        if (householdAction === 'delete') {
            deleteHousehold(householdId);
        }
    });
});
</script>
@endsection
