@extends("layouts.app")

@section("title", "Committee Records – Barangay Management System")
@section("page-title", "Committee Records & Reports")

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Committee Records & Reports</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Track committee activities and reports.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()" 
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-plus-lg"></i> Add Record
    </button>
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Committee</th>
                <th>Type</th>
                <th>Title</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse(\$records as \$record)
            <tr>
                <td>{{ \$record->committee?->name }}</td>
                <td><span class="badge" style="background:#eff6ff;color:#1a56db;">{{ \$record->record_type }}</span></td>
                <td>{{ \$record->title }}</td>
                <td>{{ \$record->recorded_at?->format("M d, Y") }}</td>
                <td>
                    <button class="btn btn-sm btn-light" onclick="editRecord({{ \$record->id }})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light" onclick="deleteRecord({{ \$record->id }})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4">No records. <a href="javascript:openCreateModal()">Add one.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Report Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="recordForm">
                @csrf
                <input type="hidden" id="recordId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Committee</label>
                        <select class="form-select" name="committee_id" required>
                            <option value="">Select Committee</option>
                            @foreach(\App\Models\Committee::all() as \$c)
                            <option value="{{ \$c->id }}">{{ \$c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Type</label>
                        <select class="form-select" name="record_type" required>
                            <option value="">Select Type</option>
                            <option value="photo">Photo</option>
                            <option value="video">Video</option>
                            <option value="activity">Activity</option>
                            <option value="accomplishment">Accomplishment</option>
                            <option value="report">Report</option>
                            <option value="attendance">Attendance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Date</label>
                        <input type="date" class="form-control" name="recorded_at" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
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
const recordModal = new bootstrap.Modal(document.getElementById("recordModal"));

function openCreateModal() {
    document.getElementById("recordForm").reset();
    document.getElementById("recordId").value = "";
    recordModal.show();
}

async function editRecord(id) {
    const res = await fetch(`/reports/${id}/edit`);
    const data = await res.json();
    if (data.success) {
        const r = data.data;
        document.getElementById("recordId").value = id;
        document.querySelector("[name=\"committee_id\"]").value = r.committee_id;
        document.querySelector("[name=\"record_type\"]").value = r.record_type;
        document.querySelector("[name=\"title\"]").value = r.title;
        document.querySelector("[name=\"recorded_at\"]").value = r.recorded_at.split("T")[0];
        document.querySelector("[name=\"description\"]").value = r.description || "";
        recordModal.show();
    }
}

async function deleteRecord(id) {
    if (!confirm("Delete this record?")) return;
    const res = await fetch(`/reports/${id}`, { method: "DELETE", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
    if (res.ok) {
        alert("Deleted successfully");
        location.reload();
    }
}

document.getElementById("recordForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("recordId").value;
    const url = id ? `/reports/${id}` : "/reports";
    const method = id ? "PUT" : "POST";
    
    const formData = new FormData(document.getElementById("recordForm"));
    const data = Object.fromEntries(formData);

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
            recordModal.hide();
            location.reload();
        } else {
            const errors = await res.json();
            alert("Error: " + JSON.stringify(errors.errors || errors.message));
        }
    } catch (err) {
        alert("Error: " + err.message);
    }
});
</script>
@endpush
