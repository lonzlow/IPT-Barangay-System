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
                        <textarea class="form-control" name="incident_description" rows="3"></textarea>
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
let currentBlotterId = null;

function openCreateModal() {
    document.getElementById('blotterForm').reset();
    document.getElementById('blotterId').value = '';
    document.querySelector('#blotterModal .modal-title').textContent = 'Add Blotter Record';
    document.getElementById('blotterForm').style.display = 'block';
    blotterModal.show();
}

async function editBlotter(id) {
    currentBlotterId = id;
    const res = await fetch(`/blotters/${id}/edit`);
    const data = await res.json();
    if (data) {
        document.getElementById('blotterId').value = data.id;
        document.querySelector('[name="case_number"]').value = data.case_number || '';
        document.querySelector('[name="complainant"]').value = data.complainant || '';
        document.querySelector('[name="respondent"]').value = data.respondent || '';
        document.querySelector('[name="incident_description"]').value = data.incident_description || '';
        document.querySelector('[name="incident_date"]').value = data.incident_date ? data.incident_date.split('T')[0] + 'T' + data.incident_date.split('T')[1].substring(0, 5) : '';
        document.querySelector('[name="status"]').value = data.status || '';
        document.querySelector('#blotterModal .modal-title').textContent = 'Edit Blotter Record';
        document.getElementById('blotterForm').style.display = 'block';
        blotterModal.show();
    }
}

async function deleteBlotter(id) {
    if (!confirm('Are you sure you want to delete this blotter record?')) return;
    try {
        const res = await fetch(`/blotters/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        if (res.ok) {
            alert('Blotter record deleted successfully');
            location.reload();
        } else {
            alert('Failed to delete record');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

function viewBlotter(id) {
    window.location.href = `/blotters/${id}`;
}

function editCurrentBlotter() {
    if (currentBlotterId) {
        editBlotter(currentBlotterId);
    }
}

document.getElementById('blotterForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('blotterId').value;
    const url = id ? `/blotters/${id}` : '/blotters';
    const method = id ? 'PUT' : 'POST';
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (res.ok) {
            alert('Blotter record saved successfully');
            blotterModal.hide();
            location.reload();
        } else {
            const errorData = await res.json();
            alert('Error: ' + JSON.stringify(errorData.errors || errorData.message));
        }
    } catch (err) {
        alert('An error occurred: ' + err.message);
    }
});

// DataTable initialization
const table = document.getElementById("blottersTable");
if ($.fn.DataTable) {
    let dt = $("#blottersTable").DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route("blotters.index") }}",
            dataSrc: "data"
        },
        columns: [
            { data: "case_number" },
            { data: "complainant" },
            { data: "respondent" },
            { data: "incident_date", render: function(data) {
                return new Date(data).toLocaleDateString();
            }},
            { data: "status", render: function(data) {
                const colors = {
                    open: "#fee2e2#dc2626",
                    ongoing: "#fef3c7#b45309",
                    resolved: "#dcfce7#15803d",
                    referred: "#f1f5f9#64748b"
                };
                return `<span class="badge" style="background:${colors[data] || "#f1f5f9"};">${data}</span>`;
            }},
            { data: "id", render: function(data) {
                return `<button class="btn btn-sm btn-light" onclick="viewBlotter(${data})"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-light" onclick="editBlotter(${data})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light" onclick="deleteBlotter(${data})"><i class="bi bi-trash"></i></button>`;
            }, orderable: false }
        ]
    });
}
</script>
@endpush
