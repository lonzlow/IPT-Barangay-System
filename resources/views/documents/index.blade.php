@extends('layouts.app')

@section('title', 'Barangay Documents')



@section('styles')
<style>
    .doc-shortcut-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }
    .doc-shortcut {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 16px 18px;
        display: flex; flex-direction: column;
        align-items: center; gap: 10px;
        cursor: pointer; transition: all .2s;
        text-align: center; text-decoration: none; color: inherit;
    }
    .doc-shortcut:hover {
        border-color: #1a56db;
        box-shadow: 0 4px 18px rgba(26,86,219,.1);
        transform: translateY(-2px); color: inherit;
    }
    .doc-shortcut .ds-icon {
        width: 52px; height: 52px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
    }
    .doc-shortcut .ds-label { font-size: 12.5px; font-weight: 700; color: #0f172a; line-height: 1.35; }
    .doc-shortcut .ds-count { font-size: 11px; color: #94a3b8; font-weight: 500; }

    .dsi-blue   { background: #eff6ff; color: #1a56db; }
    .dsi-violet { background: #f5f3ff; color: #7c3aed; }
    .dsi-green  { background: #f0fdf4; color: #16a34a; }
    .dsi-teal   { background: #f0fdfa; color: #0d9488; }
    .dsi-orange { background: #fff7ed; color: #ea580c; }

    .doc-stat {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 16px 20px; display: flex; align-items: center; gap: 14px;
    }
    .doc-stat .ds-num {
        font-size: 26px; font-weight: 800;
        font-family: 'DM Mono', monospace; color: #0f172a; line-height: 1;
    }
    .doc-stat .ds-lbl { font-size: 12px; color: #64748b; font-weight: 500; margin-top: 3px; }
    .doc-stat .ds-ico {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }

    .badge-issued    { background: #dcfce7; color: #15803d; }
    .badge-released  { background: #eff6ff; color: #1d4ed8; }
    .badge-cancelled { background: #fee2e2; color: #dc2626; }

    .preview-panel {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;
    }
    .preview-panel .preview-header {
        background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        padding: 14px 20px; display: flex; align-items: center; gap: 10px;
    }
    .preview-panel .preview-header .ph-title { font-size: 13.5px; font-weight: 700; color: #0f172a; flex: 1; }
    .preview-body { padding: 24px; }

    .doc-paper {
        background: #fff; border: 1px solid #d1d5db; border-radius: 4px;
        padding: 32px 36px; font-size: 12px; line-height: 1.8; color: #374151;
        box-shadow: 0 2px 8px rgba(0,0,0,.06); position: relative;
    }
    .doc-paper .dp-letterhead {
        text-align: center; border-bottom: 2px solid #1a56db;
        padding-bottom: 14px; margin-bottom: 18px;
    }
    .doc-paper .dp-letterhead .dp-brgy { font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: .3px; }
    .doc-paper .dp-letterhead .dp-gov  { font-size: 10.5px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
    .doc-paper .dp-title {
        text-align: center; font-size: 13px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 1.5px;
        margin: 16px 0 18px; text-decoration: underline;
    }
    .doc-paper .dp-body { text-align: justify; font-size: 12px; }
    .doc-paper .dp-sig  { margin-top: 36px; display: flex; justify-content: space-between; }
    .doc-paper .dp-sig-block { text-align: center; font-size: 11.5px; }
    .doc-paper .dp-sig-block .sig-name {
        font-weight: 700; border-top: 1px solid #374151;
        padding-top: 4px; margin-top: 32px;
    }
    .doc-paper .watermark {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%,-50%) rotate(-30deg);
        font-size: 48px; font-weight: 900;
        color: rgba(26,86,219,.05);
        pointer-events: none; letter-spacing: 4px;
        text-transform: uppercase; white-space: nowrap;
    }

    .form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; }
    .form-card .fc-heading { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .form-card .fc-sub     { font-size: 12px; color: #64748b; margin-bottom: 18px; }
</style>
@endsection

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Document Issuance</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Issue, track, and manage barangay certificates and clearances.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13px;font-weight:600;padding:8px 16px;">
            <i class="bi bi-clock-history"></i> Request Queue
            <span class="badge bg-warning text-dark ms-1">5</span>
        </button>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Issue Document
        </button>
    </div>
</div>

{{-- ── SHORTCUT BUTTONS ── --}}
<div class="section-heading">Issue Document</div>
<div class="doc-shortcut-grid mb-4">
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-blue"><i class="bi bi-heart-pulse-fill"></i></div>
        <div class="ds-label">Indigency<br>Certificate</div>
        <div class="ds-count">148 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-violet"><i class="bi bi-house-fill"></i></div>
        <div class="ds-label">Residency<br>Certificate</div>
        <div class="ds-count">95 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-green"><i class="bi bi-patch-check-fill"></i></div>
        <div class="ds-label">Barangay<br>Clearance</div>
        <div class="ds-count">212 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-teal"><i class="bi bi-award-fill"></i></div>
        <div class="ds-label">Good Moral<br>Certificate</div>
        <div class="ds-count">63 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-orange"><i class="bi bi-shop-window"></i></div>
        <div class="ds-label">Business<br>Clearance</div>
        <div class="ds-count">31 issued this month</div>
    </a>
</div>

{{-- ── STAT MINI CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#eff6ff;color:#1a56db;"><i class="bi bi-file-earmark-check-fill"></i></div>
            <div><div class="ds-num">549</div><div class="ds-lbl">Issued this month</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="ds-num">5</div><div class="ds-lbl">Pending requests</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-printer-fill"></i></div>
            <div><div class="ds-num">12</div><div class="ds-lbl">Ready to print</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-circle-fill"></i></div>
            <div><div class="ds-num">3</div><div class="ds-lbl">Cancelled today</div></div>
        </div>
    </div>
</div>

{{-- ── RECENT DOCS + PENDING ── --}}
<div class="row g-3 mb-4">

    {{-- Recent Documents Issued --}}
    <div class="col-lg-12">
        <div class="table-card h-100">
            <div class="table-header">
                <span class="heading">Recent Documents Issued</span>
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size:12.5px;border-radius:7px;">
                    <i class="bi bi-download"></i> Export
                </button>
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
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── CUSTOM LETTER + PRINT PREVIEW ── --}}
<div class="section-heading">Quick Actions</div>
<div class="row g-3">

    <div class="col-lg-5">
        <div class="form-card h-100">
            <div class="fc-heading"><i class="bi bi-pencil-square me-2 text-primary"></i>Generate Custom Letter / Document</div>
            <div class="fc-sub">Fill in the fields below to produce a custom barangay document.</div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Resident Name</label>
                <input type="text" class="form-control" style="font-size:13.5px;border-radius:8px;" placeholder="Search or type resident name…">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Document Type</label>
                <select class="form-select" style="font-size:13.5px;border-radius:8px;">
                    <option value="">— Select document type —</option>
                    <option>Indigency Certificate</option>
                    <option>Residency Certificate</option>
                    <option>Barangay Clearance</option>
                    <option>Good Moral Character Certificate</option>
                    <option>Business Clearance</option>
                    <option>Custom Letter</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Purpose</label>
                <input type="text" class="form-control" style="font-size:13.5px;border-radius:8px;" placeholder="e.g. For employment, scholarship, etc.">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Additional Notes <span style="font-weight:400;color:#94a3b8;">(optional)</span></label>
                <textarea class="form-control" rows="3" style="font-size:13px;border-radius:8px;resize:none;" placeholder="Any special instructions or inclusions…"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2"
                        style="border-radius:8px;font-size:13.5px;font-weight:600;padding:10px;">
                    <i class="bi bi-file-earmark-plus"></i> Generate Document
                </button>
                <button class="btn btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius:8px;font-size:13px;padding:10px 14px;" title="Clear form">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="preview-panel h-100">
            <div class="preview-header">
                <i class="bi bi-eye-fill text-primary"></i>
                <span class="ph-title">Print-Ready Preview</span>
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                        style="border-radius:7px;font-size:12px;font-weight:600;">
                    <i class="bi bi-printer-fill"></i> Print
                </button>
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 ms-1"
                        style="border-radius:7px;font-size:12px;font-weight:600;">
                    <i class="bi bi-download"></i> PDF
                </button>
            </div>
            <div class="preview-body">
                <div class="doc-paper">
                    <div class="watermark">PREVIEW</div>
                    <div class="dp-letterhead">
                        <div class="dp-gov">Republic of the Philippines · City of Sample · District I</div>
                        <div class="dp-brgy">BARANGAY UNO</div>
                        <div style="font-size:10.5px;color:#64748b;">Office of the Punong Barangay</div>
                    </div>
                    <div class="dp-title">Barangay Clearance</div>
                    <div class="dp-body">
                        <p>TO WHOM IT MAY CONCERN:</p>
                        <p>This is to certify that <strong>JUAN DELA CRUZ</strong>, of legal age, Filipino citizen, and a bonafide resident of <strong>123 Mabini Street, Purok 1, Barangay Uno</strong>, is personally known to this office to be of good moral character and has no derogatory record on file with the Barangay.</p>
                        <p>This certification is issued upon the request of the above-named person for <strong>employment purposes</strong> and for whatever legal purpose it may serve.</p>
                        <p>Issued this <strong>24th day of February 2025</strong> at Barangay Uno.</p>
                    </div>
                    <div class="dp-sig">
                        <div class="dp-sig-block">
                            <div>Requested by:</div>
                            <div class="sig-name">JUAN DELA CRUZ</div>
                            <div style="font-size:10.5px;color:#64748b;">Requesting Party</div>
                        </div>
                        <div class="dp-sig-block">
                            <div>Certified by:</div>
                            <div class="sig-name">HON. SAMPLE CAPTAIN</div>
                            <div style="font-size:10.5px;color:#64748b;">Punong Barangay</div>
                        </div>
                    </div>
                    <div style="margin-top:20px;font-size:10px;color:#9ca3af;text-align:right;">
                        Control No.: <strong>BRG-2025-0842</strong> &nbsp;·&nbsp; OR No.: __________ &nbsp;·&nbsp; Fee: ₱50.00
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Document Form Modal --}}
@include('documents.partials.form-modal')

@endsection

@section('scripts')
<script>
    // Wait for jQuery and axios to be available
    function waitForDependencies(callback, attempts = 0) {
        if (typeof window.$ !== 'undefined' && typeof window.axios !== 'undefined') {
            callback();
        } else if (attempts < 50) {
            setTimeout(() => waitForDependencies(callback, attempts + 1), 100);
        } else {
            console.error('jQuery and/or axios did not load');
        }
    }

    // ───────────────────────────────────────────
    // INITIALIZATION - All code runs after dependencies load
    // ───────────────────────────────────────────
    waitForDependencies(function() {
        const $ = window.$;
        const axios = window.axios;

        // Global variables
        let documentsTable;
        let editingDocumentId = null;
        let documentForm;
        let submitFormBtn;
        let modal;

        // ───────────────────────────────────────────
        // HELPER FUNCTIONS
        // ───────────────────────────────────────────
        function clearFormErrors() {
            document.querySelectorAll('.text-danger').forEach(el => {
                el.classList.add('d-none');
                el.textContent = '';
            });
        }

        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => alert.remove());
            }, 5000);
        }

        function openCreateModal() {
            editingDocumentId = null;
            documentForm.reset();
            document.getElementById('documentId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('submitBtnText').textContent = 'Issue Document';
            document.getElementById('templateGroup').classList.remove('d-none');
            document.getElementById('statusGroup').classList.add('d-none');
            document.getElementById('residentSelect').disabled = false;
            document.getElementById('templateSelect').disabled = false;
            document.getElementById('documentFormModalLabel').textContent = 'Issue New Document';
            clearFormErrors();
            modal.show();
        }

        function openEditModal(documentId) {
            editingDocumentId = documentId;
            document.getElementById('documentId').value = documentId;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('submitBtnText').textContent = 'Save Changes';
            document.getElementById('templateGroup').classList.add('d-none');
            document.getElementById('statusGroup').classList.remove('d-none');
            document.getElementById('residentSelect').disabled = true;
            document.getElementById('templateSelect').disabled = true;
            document.getElementById('documentFormModalLabel').textContent = 'Edit Document';
            clearFormErrors();

            axios.get(`/documents/${documentId}`, {
                headers: { 'Accept': 'application/json' }
            }).then(response => {
                const doc = response.data;
                document.querySelector('input[name="purpose"]').value = doc.purpose || '';
                document.querySelector('input[name="issued_by"]').value = doc.issued_by || '';
                document.querySelector('select[name="status"]').value = doc.status || 'Issued';
                modal.show();
            }).catch(error => {
                showAlert('Error loading document details', 'danger');
            });
        }

        function deleteDocument(documentId) {
            if (!confirm('Are you sure you want to delete this document?')) return;

            axios.delete(`/documents/${documentId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                }
            }).then(response => {
                showAlert(response.data.message, 'success');
                documentsTable.ajax.reload();
            }).catch(error => {
                showAlert(error.response?.data?.message || 'An error occurred', 'danger');
            });
        }

        // Make functions globally available
        window.openCreateModal = openCreateModal;
        window.openEditModal = openEditModal;
        window.deleteDocument = deleteDocument;

        // ───────────────────────────────────────────
        // DOM READY INITIALIZATION
        // ───────────────────────────────────────────
        $(document).ready(function() {
            // Initialize Axios defaults
            axios.defaults.baseURL = '{{ url("/") }}';

            // Cache DOM elements
            documentForm = document.getElementById('documentForm');
            submitFormBtn = document.getElementById('submitFormBtn');
            modal = new bootstrap.Modal(document.getElementById('documentFormModal'));

            // Initialize DataTable
            documentsTable = $('#documentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("documents.data") }}',
                columns: [
                    { data: 'reference_number', name: 'reference_number' },
                    { data: 'resident_name', name: 'resident_name' },
                    { data: 'template_name', name: 'template_name' },
                    { data: 'issued_date_formatted', name: 'issued_date' },
                    { data: 'valid_until_formatted', name: 'valid_until' },
                    { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']],
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                responsive: true,
                language: {
                    search: "Search documents:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ documents",
                    infoEmpty: "No documents available",
                    infoFiltered: "(filtered from _MAX_ total documents)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });

            // Form submission handler
            submitFormBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!documentForm.checkValidity()) {
                    documentForm.reportValidity();
                    return;
                }

                const formData = new FormData(documentForm);
                const params = new URLSearchParams(formData);
                
                let url = '/documents';
                let method = 'POST';
                
                if (editingDocumentId) {
                    url = `/documents/${editingDocumentId}`;
                    method = 'PUT';
                    params.append('_method', 'PUT');
                }

                document.getElementById('formLoading').classList.remove('d-none');
                submitFormBtn.disabled = true;

                axios({
                    method: method === 'PUT' ? 'post' : method,
                    url: url,
                    data: params,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    }
                }).then(response => {
                    showAlert(response.data.message, 'success');
                    modal.hide();
                    setTimeout(() => {
                        documentsTable.ajax.reload();
                    }, 800);
                }).catch(error => {
                    if (error.response && error.response.status === 422) {
                        const errors = error.response.data.errors || {};
                        Object.keys(errors).forEach(field => {
                            const errorElement = document.getElementById(`${field}-error`);
                            if (errorElement) {
                                errorElement.textContent = errors[field][0];
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

            // Template description handler
            const templateSelect = document.getElementById('templateSelect');
            if (templateSelect) {
                templateSelect.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    const desc = option.dataset.description;
                    document.getElementById('template-desc').textContent = desc || '';
                });
            }
        });
    });
</script>
@endsection