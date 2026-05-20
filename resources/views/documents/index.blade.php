@extends('layouts.app')

@section('title', 'Barangay Documents')
@section('page-title', 'Document Issuance')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .doc-shortcut-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:14px; }
        .doc-shortcut { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:18px 16px; display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center; cursor:pointer; transition:.18s; }
        .doc-shortcut:hover { border-color:#1a56db; box-shadow:0 4px 18px rgba(26,86,219,.1); transform:translateY(-1px); }
        .doc-shortcut .ds-icon { width:48px; height:48px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:21px; }
        .doc-shortcut .ds-label { font-size:12.5px; font-weight:700; color:#0f172a; line-height:1.35; }
        .doc-shortcut .ds-count { font-size:11px; color:#64748b; }
        .doc-stat { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .doc-stat .ds-num { font-size:25px; font-weight:800; font-family:'DM Mono',monospace; color:#0f172a; line-height:1; }
        .doc-stat .ds-lbl { font-size:12px; color:#64748b; font-weight:500; margin-top:3px; }
        .doc-stat .ds-ico { width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .form-panel, .preview-panel { background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; }
        .panel-header { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:14px 18px; display:flex; align-items:center; gap:10px; }
        .panel-title { font-size:13.5px; font-weight:700; color:#0f172a; flex:1; }
        .panel-body { padding:18px; }
        .preview-body { padding:18px; background:#f8fafc; min-height:560px; overflow:auto; }
        .preview-empty { height:520px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:13px; text-align:center; border:1px dashed #cbd5e1; border-radius:8px; background:#fff; }
        .select2-container .select2-selection--single { height:38px; border:1px solid #cbd5e1; border-radius:8px; display:flex; align-items:center; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px; padding-left:12px; color:#1e293b; font-size:13px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
        .select2-container--open { z-index:1065; }
    </style>
@endsection

@section('content')
    @php
        $issuerResident = auth()->user()?->official?->resident;
        $defaultIssuer = $issuerResident
            ? trim($issuerResident->first_name . ' ' . $issuerResident->last_name)
            : (auth()->user()?->email ?? 'Barangay Official');
    @endphp

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-800 mb-1" style="font-size:18px;">Document Issuance</h5>
            <p class="mb-0" style="font-size:13px;color:#64748b;">Issue, preview, print, and track barangay certificates and clearances.</p>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Issue Document
        </button>
    </div>

    <div class="section-heading">Issue Document</div>
    <div class="doc-shortcut-grid mb-4">
        @foreach($templates as $template)
            @php
                $name = strtolower($template->name);
                $icon = str_contains($name, 'indigency') ? 'bi-heart-pulse-fill' :
                    (str_contains($name, 'residency') ? 'bi-house-fill' :
                    (str_contains($name, 'good moral') ? 'bi-award-fill' :
                    (str_contains($name, 'business') ? 'bi-shop-window' : 'bi-patch-check-fill')));
                $color = str_contains($name, 'business') ? '#fff7ed;color:#ea580c' :
                    (str_contains($name, 'good moral') ? '#f0fdfa;color:#0d9488' :
                    (str_contains($name, 'residency') ? '#f5f3ff;color:#7c3aed' :
                    (str_contains($name, 'indigency') ? '#eff6ff;color:#1a56db' : '#f0fdf4;color:#16a34a')));
            @endphp
            <button type="button" class="doc-shortcut" data-template-id="{{ $template->id }}">
                <div class="ds-icon" style="background:{{ explode(';', $color)[0] }};{{ explode(';', $color)[1] }};">
                    <i class="bi {{ $icon }}"></i>
                </div>
                <div class="ds-label">{{ $template->name }}</div>
                <div class="ds-count">{{ number_format($templateStats[$template->id] ?? 0) }} issued this month</div>
            </button>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="ds-ico" style="background:#eff6ff;color:#1a56db;"><i class="bi bi-file-earmark-check-fill"></i></div>
                <div><div class="ds-num">{{ number_format($stats['issued_this_month']) }}</div><div class="ds-lbl">Issued this month</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="ds-ico" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-printer-fill"></i></div>
                <div><div class="ds-num">{{ number_format($stats['ready_to_print']) }}</div><div class="ds-lbl">Ready to print</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="ds-ico" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-circle-fill"></i></div>
                <div><div class="ds-num">{{ number_format($stats['revoked_today']) }}</div><div class="ds-lbl">Revoked today</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="ds-ico" style="background:#f8fafc;color:#475569;"><i class="bi bi-archive-fill"></i></div>
                <div><div class="ds-num">{{ number_format($stats['total_issued']) }}</div><div class="ds-lbl">Total records</div></div>
            </div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="table-header">
            <span class="heading">Recent Documents Issued</span>
        </div>
        <div class="px-3 pt-3 pb-2 border-bottom" style="background:#fff;">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Filter by Resident</label>
                    <select id="residentFilterSelect" class="form-select" style="font-size:13px;border-radius:8px;">
                        <option value="">All residents</option>
                        @foreach($residents as $resident)
                            <option value="{{ $resident->id }}">{{ $resident->first_name }} {{ $resident->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Filter by Document Type</label>
                    <select id="templateFilterSelect" class="form-select" style="font-size:13px;border-radius:8px;">
                        <option value="">All document types</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-grow-1" id="clearDocumentFilters" style="border-radius:8px;font-size:13px;">Clear filters</button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="documentsTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reference No.</th>
                        <th>Resident</th>
                        <th>Document Type</th>
                        <th>Date Issued</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th style="width:150px;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="section-heading">Quick Preview</div>
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="form-panel h-100">
                <div class="panel-header">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span class="panel-title">Generate Custom Letter / Document</span>
                </div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12.5px;font-weight:600;">Resident</label>
                        <select id="quickResidentSelect" class="form-select">
                            <option value="">-- Select Resident --</option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}">{{ $resident->first_name }} {{ $resident->last_name }} ({{ $resident->household?->purok?->purok_name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12.5px;font-weight:600;">Document Type</label>
                        <select id="quickTemplateSelect" class="form-select">
                            <option value="">-- Select Document Type --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-business="{{ str_contains(strtolower($template->name), 'business') ? '1' : '0' }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="quickBusinessGroup">
                        <label class="form-label" style="font-size:12.5px;font-weight:600;">Business</label>
                        <select id="quickBusinessSelect" class="form-select">
                            <option value="">-- Select Business --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12.5px;font-weight:600;">Purpose</label>
                        <input type="text" id="quickPurpose" class="form-control" style="font-size:13.5px;border-radius:8px;" placeholder="e.g. For employment, scholarship, etc.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12.5px;font-weight:600;">Additional Notes</label>
                        <textarea id="quickNotes" class="form-control" rows="3" style="font-size:13px;border-radius:8px;resize:none;" placeholder="Any special instructions or inclusions"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2" id="quickGenerateBtn"
                            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:10px;">
                            <i class="bi bi-file-earmark-plus"></i> Generate Preview
                        </button>
                        <button class="btn btn-outline-secondary" id="quickClearBtn" style="border-radius:8px;font-size:13px;padding:10px 14px;" title="Clear form">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="preview-panel h-100">
                <div class="panel-header">
                    <i class="bi bi-eye-fill text-primary"></i>
                    <span class="panel-title">Print-Ready Preview</span>
                    <button class="btn btn-sm btn-outline-primary" id="printPreviewBtn" style="border-radius:7px;font-size:12px;font-weight:600;">
                        <i class="bi bi-printer-fill"></i> Print
                    </button>
                </div>
                <div class="preview-body" id="previewOutput">
                    <div class="preview-empty">Select a resident and document type to generate a print-ready preview.</div>
                </div>
            </div>
        </div>
    </div>

    @include('documents.partials.form-modal')
@endsection

@section('scripts')
    <script>
        function waitForDependencies(callback, attempts = 0) {
            if (typeof window.$ !== 'undefined' && typeof window.axios !== 'undefined') {
                callback();
            } else if (attempts < 50) {
                setTimeout(() => waitForDependencies(callback, attempts + 1), 100);
            } else {
                console.error('jQuery and/or axios did not load');
            }
        }

        waitForDependencies(function () {
            const $ = window.$;
            const axios = window.axios;
            let documentsTable;
            let editingDocumentId = null;
            let documentForm;
            let submitFormBtn;
            let modal;
            let selectedBusinessId = null;

            axios.defaults.baseURL = '{{ url("/") }}';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
            axios.defaults.headers.common['Accept'] = 'application/json';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            function initSelect2() {
                if (typeof $.fn.select2 !== 'function') return;

                $('#residentSelect, #templateSelect, #businessSelect').select2({
                    width: '100%',
                    dropdownParent: $('#documentFormModal'),
                    allowClear: true,
                });

                $('#residentFilterSelect, #templateFilterSelect, #quickResidentSelect, #quickTemplateSelect, #quickBusinessSelect').select2({
                    width: '100%',
                    allowClear: true,
                });
            }

            function setSelect2Value(selector, value) {
                $(selector).val(value || null).trigger('change');
            }

            function clearFormErrors() {
                document.querySelectorAll('#documentForm .text-danger').forEach(el => {
                    el.classList.add('d-none');
                    el.textContent = '';
                });
            }

            function showAlert(message, type = 'success') {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position:fixed;top:20px;right:20px;z-index:9999;max-width:420px;">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', alertHtml);
                setTimeout(() => document.querySelectorAll('.alert').forEach(alert => alert.remove()), 5000);
            }

            function isBusinessTemplate(selector) {
                const option = document.querySelector(`${selector} option:checked`);
                if (!option) return false;
                return option.dataset.business === '1' || option.textContent.toLowerCase().includes('business');
            }

            function resetBusinessSelect(selector) {
                const select = document.querySelector(selector);
                select.innerHTML = '<option value="">-- Select Business --</option>';
                $(selector).val(null).trigger('change');
            }

            async function loadBusinesses(residentId, selector, selectedId = null) {
                resetBusinessSelect(selector);
                if (!residentId) return;

                const response = await axios.get(`/documents/residents/${residentId}/businesses`);
                const select = document.querySelector(selector);
                response.data.forEach(business => {
                    select.append(new Option(`${business.business_name} (${business.business_type})`, business.id));
                });
                if (selectedId) {
                    $(selector).val(selectedId).trigger('change');
                }
            }

            async function syncBusinessGroup() {
                const businessRequired = isBusinessTemplate('#templateSelect');
                document.getElementById('businessGroup').classList.toggle('d-none', !businessRequired);
                document.getElementById('businessSelect').required = businessRequired;
                if (!businessRequired) {
                    resetBusinessSelect('#businessSelect');
                    return;
                }
                await loadBusinesses($('#residentSelect').val(), '#businessSelect', selectedBusinessId);
            }

            async function syncQuickBusinessGroup() {
                const businessRequired = isBusinessTemplate('#quickTemplateSelect');
                document.getElementById('quickBusinessGroup').classList.toggle('d-none', !businessRequired);
                if (!businessRequired) {
                    resetBusinessSelect('#quickBusinessSelect');
                    return;
                }
                await loadBusinesses($('#quickResidentSelect').val(), '#quickBusinessSelect');
            }

            function openCreateModal(templateId = null) {
                editingDocumentId = null;
                selectedBusinessId = null;
                documentForm.reset();
                document.getElementById('documentId').value = '';
                document.getElementById('formMethod').value = 'POST';
                document.getElementById('submitBtnText').textContent = 'Issue Document';
                document.getElementById('statusGroup').classList.add('d-none');
                document.getElementById('documentFormModalLabel').textContent = 'Issue New Document';
                clearFormErrors();
                setSelect2Value('#residentSelect', '');
                setSelect2Value('#templateSelect', templateId || '');
                setSelect2Value('#businessSelect', '');
                syncBusinessGroup();
                modal.show();
            }

            function openEditModal(documentId) {
                editingDocumentId = documentId;
                selectedBusinessId = null;
                document.getElementById('documentId').value = documentId;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('submitBtnText').textContent = 'Save Changes';
                document.getElementById('statusGroup').classList.remove('d-none');
                document.getElementById('documentFormModalLabel').textContent = 'Edit Document';
                clearFormErrors();

                axios.get(`/documents/${documentId}`).then(async response => {
                    const doc = response.data;
                    selectedBusinessId = doc.business_id || null;
                    document.querySelector('input[name="purpose"]').value = doc.purpose || '';
                    document.querySelector('textarea[name="additional_notes"]').value = doc.additional_notes || '';
                    document.querySelector('input[name="issued_by"]').value = doc.issued_by || '';
                    document.querySelector('select[name="status"]').value = doc.status || 'Issued';

                    setSelect2Value('#residentSelect', doc.resident_id || '');
                    setSelect2Value('#templateSelect', doc.document_template_id || '');
                    await syncBusinessGroup();
                    modal.show();
                }).catch(() => showAlert('Error loading document details', 'danger'));
            }

            function deleteDocument(documentId) {
                if (!confirm('Are you sure you want to delete this document?')) return;

                axios.delete(`/documents/${documentId}`).then(response => {
                    showAlert(response.data.message);
                    documentsTable.ajax.reload();
                }).catch(error => {
                    showAlert(error.response?.data?.message || 'An error occurred', 'danger');
                });
            }

            async function generatePreviewFromQuickForm() {
                const payload = new URLSearchParams();
                payload.append('_token', csrfToken);
                payload.append('resident_id', $('#quickResidentSelect').val() || '');
                payload.append('document_template_id', $('#quickTemplateSelect').val() || '');
                payload.append('business_id', $('#quickBusinessSelect').val() || '');
                payload.append('purpose', document.getElementById('quickPurpose').value || '');
                payload.append('additional_notes', document.getElementById('quickNotes').value || '');
                payload.append('issued_by', @json($defaultIssuer));

                const output = document.getElementById('previewOutput');
                output.innerHTML = '<div class="preview-empty">Generating preview...</div>';

                try {
                    const response = await axios.post('{{ route("documents.preview") }}', payload);
                    output.innerHTML = response.data.html;
                } catch (error) {
                    const errors = error.response?.data?.errors || {};
                    const firstError = Object.values(errors)[0]?.[0] || 'Unable to generate preview. Please complete the required fields.';
                    output.innerHTML = `<div class="preview-empty text-danger">${firstError}</div>`;
                }
            }

            window.openCreateModal = openCreateModal;
            window.openEditModal = openEditModal;
            window.deleteDocument = deleteDocument;

            $(document).ready(function () {
                documentForm = document.getElementById('documentForm');
                submitFormBtn = document.getElementById('submitFormBtn');
                modal = new bootstrap.Modal(document.getElementById('documentFormModal'));
                initSelect2();

                documentsTable = $('#documentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("documents.data") }}',
                        data: data => {
                            data.resident_id = $('#residentFilterSelect').val();
                            data.document_template_id = $('#templateFilterSelect').val();
                        },
                        dataSrc: 'data',
                    },
                    columns: [
                        { data: 'reference_number', name: 'reference_number' },
                        { data: 'resident_name', name: 'resident.first_name' },
                        { data: 'template_name', name: 'template.name' },
                        { data: 'issued_date_formatted', name: 'issued_date' },
                        { data: 'valid_until_formatted', name: 'valid_until' },
                        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ],
                    order: [[3, 'desc']],
                    pageLength: 10,
                    responsive: true,
                });

                $('#residentFilterSelect, #templateFilterSelect').on('change', () => documentsTable.ajax.reload());
                $('#clearDocumentFilters').on('click', function () {
                    setSelect2Value('#residentFilterSelect', '');
                    setSelect2Value('#templateFilterSelect', '');
                    documentsTable.ajax.reload();
                });

                $('.doc-shortcut').on('click', function () {
                    openCreateModal(this.dataset.templateId);
                });

                $('#residentSelect, #templateSelect').on('change', function () {
                    selectedBusinessId = $('#businessSelect').val();
                    syncBusinessGroup();
                });

                $('#quickResidentSelect, #quickTemplateSelect').on('change', syncQuickBusinessGroup);
                $('#quickGenerateBtn').on('click', generatePreviewFromQuickForm);
                $('#quickClearBtn').on('click', function () {
                    setSelect2Value('#quickResidentSelect', '');
                    setSelect2Value('#quickTemplateSelect', '');
                    setSelect2Value('#quickBusinessSelect', '');
                    document.getElementById('quickPurpose').value = '';
                    document.getElementById('quickNotes').value = '';
                    document.getElementById('quickBusinessGroup').classList.add('d-none');
                    document.getElementById('previewOutput').innerHTML = '<div class="preview-empty">Select a resident and document type to generate a print-ready preview.</div>';
                });

                $('#printPreviewBtn').on('click', function () {
                    const content = document.getElementById('previewOutput').innerHTML;
                    const win = window.open('', '_blank');
                    win.document.write(`<html><head><title>Document Preview</title></head><body>${content}</body></html>`);
                    win.document.close();
                    win.focus();
                    win.print();
                });

                submitFormBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    clearFormErrors();

                    if (!documentForm.checkValidity()) {
                        documentForm.reportValidity();
                        return;
                    }

                    const params = new URLSearchParams(new FormData(documentForm));
                    let url = '/documents';

                    if (editingDocumentId) {
                        url = `/documents/${editingDocumentId}`;
                        params.append('_method', 'PUT');
                    }

                    document.getElementById('formLoading').classList.remove('d-none');
                    submitFormBtn.disabled = true;

                    axios.post(url, params).then(response => {
                        showAlert(response.data.message);
                        modal.hide();
                        documentsTable.ajax.reload();
                    }).catch(error => {
                        if (error.response?.status === 422) {
                            Object.entries(error.response.data.errors || {}).forEach(([field, messages]) => {
                                const errorElement = document.getElementById(`${field}-error`);
                                if (errorElement) {
                                    errorElement.textContent = messages[0];
                                    errorElement.classList.remove('d-none');
                                }
                            });
                            showAlert('Please fix the validation errors', 'warning');
                        } else {
                            showAlert(error.response?.data?.message || 'An error occurred', 'danger');
                        }
                    }).finally(() => {
                        document.getElementById('formLoading').classList.add('d-none');
                        submitFormBtn.disabled = false;
                    });
                });
            });
        });
    </script>
@endsection
