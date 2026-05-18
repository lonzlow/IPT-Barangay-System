@extends("layouts.app")

@section("title", "Committees – Barangay Management System")
@section("page-title", "Committees Management")

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Committees Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Manage committees and track records.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()" 
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-plus-lg"></i> Add Committee
    </button>
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Head Official</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($committees as $committee)
            <tr>
                <td style="font-weight:600;">{{ $committee->name }}</td>
                <td>{{ $committee->headOfficial?->resident?->first_name ?? "—" }}</td>
                <td>{{ $committee->description }}</td>
                <td>
                    <button class="btn btn-sm btn-light" onclick="editCommittee({{ $committee->id }})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light" onclick="deleteCommittee({{ $committee->id }})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center py-4">No committees. <a href="javascript:openCreateModal()">Add one.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="committeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Committee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="committeeForm">
                @csrf
                <input type="hidden" id="committeeId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Name</label>
                        <input type="text" class="form-control" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Head Official</label>
                        <select class="form-select" name="head_official_id">
                            <option value="">Select Official</option>
                            @foreach(\App\Models\Official::where("is_active", true)->get() as $o)
                            <option value="{{ $o->id }}">{{ $o->resident?->first_name }}</option>
                            @endforeach
                        </select>
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
const committeeModal = new bootstrap.Modal(document.getElementById("committeeModal"));

function openCreateModal() {
    document.getElementById("committeeForm").reset();
    document.getElementById("committeeId").value = "";
    committeeModal.show();
}

async function editCommittee(id) {
    const res = await fetch(`/committees/${id}/edit`);
    const data = await res.json();
    if (data.success) {
        const c = data.data;
        document.getElementById("committeeId").value = id;
        document.querySelector("[name=\"name\"]").value = c.name;
        document.querySelector("[name=\"head_official_id\"]").value = c.head_official_id || "";
        document.querySelector("[name=\"description\"]").value = c.description || "";
        committeeModal.show();
    }
}

async function deleteCommittee(id) {
    if (!confirm("Delete this committee?")) return;
    const res = await fetch(`/committees/${id}`, { method: "DELETE", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
    if (res.ok) {
        alert("Deleted successfully");
        location.reload();
    }
}

document.getElementById("committeeForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("committeeId").value;
    const url = id ? `/committees/${id}` : "/committees";
    const method = id ? "PUT" : "POST";
    
    const formData = new FormData(document.getElementById("committeeForm"));
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
            committeeModal.hide();
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
