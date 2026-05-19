@extends("layouts.app")

@section("title", "Officials & Staff – Barangay Management System")
@section("page-title", "Officials & Staff Management")

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Officials & Staff Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Manage barangay officials, staff profiles, and term tracking.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()" 
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-person-plus-fill"></i> Add Official
    </button>
</div>

<div id="alertContainer"></div>

<div class="table-card">
    <table id="officialsTable" class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Term Start</th>
                <th>Term End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($officials as $official)
            <tr>
                <td style="font-weight:600;font-size:13px;">{{ $official->resident?->first_name }} {{ $official->resident?->last_name }}</td>
                <td>{{ $official->position }}</td>
                <td>{{ $official->term_start?->format("M d, Y") }}</td>
                <td>{{ $official->term_end?->format("M d, Y") }}</td>
                <td><span class="badge" style="background:{{ $official->is_active ? "#dcfce7" : "#f1f5f9" }};color:{{ $official->is_active ? "#15803d" : "#64748b" }};">{{ $official->is_active ? "Active" : "Inactive" }}</span></td>
                <td>
                    <button class="btn btn-sm btn-light" onclick="viewOfficial({{ $official->id }})"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-light" onclick="editOfficial({{ $official->id }})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light" onclick="deleteOfficial({{ $official->id }})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4">No officials found. <a href="javascript:openCreateModal()">Add one.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="officialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Official Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="officialForm">
                @csrf
                <input type="hidden" id="officialId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Resident</label>
                        <select class="form-select" name="resident_id" required>
                            <option value="">Select Resident</option>
                            @foreach(\App\Models\Resident::all() as $resident)
                            <option value="{{ $resident->id }}">{{ $resident->first_name }} {{ $resident->last_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Position</label>
                        <input type="text" class="form-control" name="position" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Term Start</label>
                            <input type="date" class="form-control" name="term_start" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Term End</label>
                            <input type="date" class="form-control" name="term_end">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1"> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push("scripts")
<script>
const officialModal = new bootstrap.Modal(document.getElementById("officialModal"));
let currentOfficialId = null;

function openCreateModal() {
    currentOfficialId = null;
    document.getElementById("officialForm").reset();
    document.getElementById("officialId").value = "";
    document.querySelector("#officialModal .modal-title").textContent = "Add Official";
    officialModal.show();
}

async function editOfficial(id) {
    currentOfficialId = id;
    const res = await fetch(`/officials/${id}/edit`);
    const data = await res.json();
    if (data.success) {
        const o = data.data;
        document.getElementById("officialId").value = id;
        document.querySelector("[name=\"resident_id\"]").value = o.resident_id;
        document.querySelector("[name=\"position\"]").value = o.position;
        document.querySelector("[name=\"term_start\"]").value = o.term_start.split("T")[0];
        document.querySelector("[name=\"term_end\"]").value = o.term_end ? o.term_end.split("T")[0] : "";
        document.querySelector("[name=\"is_active\"]").checked = o.is_active;
        document.querySelector("#officialModal .modal-title").textContent = "Edit Official";
        officialModal.show();
    }
}

async function deleteOfficial(id) {
    if (!confirm("Delete this official?")) return;
    const res = await fetch(`/officials/${id}`, { method: "DELETE", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
    if (res.ok) {
        alert("Deleted successfully");
        location.reload();
    }
}

function viewOfficial(id) {
    window.location.href = `/officials/${id}`;
}

document.getElementById("officialForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("officialId").value;
    const url = id ? `/officials/${id}` : "/officials";
    const method = id ? "PUT" : "POST";
    
    const formData = new FormData(document.getElementById("officialForm"));
    const data = Object.fromEntries(formData);
    data.is_active = document.querySelector("[name=\"is_active\"]").checked ? 1 : 0;

    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify(data)
        });
        
        if (res.ok) {
            alert("Saved successfully");
            officialModal.hide();
            location.reload();
        } else {
            const errors = await res.json();
            alert("Error: " + JSON.stringify(errors.errors));
        }
    } catch (err) {
        alert("Error: " + err.message);
    }
});
</script>
@endpush
