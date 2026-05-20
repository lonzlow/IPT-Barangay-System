@extends("layouts.app")

@section("title", "Business Records - Barangay Management System")
@section("page-title", "Business Clearance & Records")

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Business Records</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Manage barangay business permits and clearances.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-plus-lg"></i> Add Business
    </button>
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Business Name</th>
                <th>Owner</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($businesses as $business)
            <tr>
                <td class="fw-600">{{ $business->business_name }}</td>
                <td>
                    @foreach($business->business_owners as $owner)
                        @if($owner->resident_id)
                            {{-- Owner is a resident --}}
                            {{ $owner->resident->first_name }} {{ $owner->resident->middle_name }} {{ $owner->resident->last_name }} {{ $owner->resident->suffix }}
                        @else
                            {{-- Owner is not a resident --}}
                            {{ $owner->organization_name ?? ($owner->first_name . ' ' . $owner->middle_name . ' ' . $owner->last_name . ' ' . $owner->suffix) }}
                            <br>
                            <small>{{ $owner->contact_number }} | {{ $owner->email }} | {{ $owner->address }}</small>
                        @endif
                        <br>
                    @endforeach
                </td>
                <td>{{ $business->business_type }}</td>
                <td>
                    <span class="badge {{ $business->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                        {{ ucfirst($business->status) }}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-light" onclick="editBusiness({{ $business->id }})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light" onclick="deleteBusiness({{ $business->id }})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4">No businesses found. <a href="javascript:openCreateModal()">Add one.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="businessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Business Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="businessForm">
                @csrf
                <input type="hidden" id="businessId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Business Name</label>
                        <input type="text" class="form-control" name="business_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Owner Name</label>
                        <input type="text" class="form-control" name="owner_name" required>
                    </div>
                    <div class="mb-3">
                        @php
                            $types = ['Supermarket', 'Laundry Service', 'Pharmacy', 'Restaurant', 'Retail Service', 'Sari-Sari']
                        @endphp
                        <label class="form-label fw-600">Business Type</label>
                        <select name="business_type" class="form-select">
                            @foreach ($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Address</label>
                        <textarea class="form-control" name="business_address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Status</label>
                        <select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="expired">Expired</option>
                        </select>
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

@endsection

@push("scripts")
<script>
const businessModal = new bootstrap.Modal(document.getElementById("businessModal"));

window.openCreateModal = () => {
    document.getElementById("businessForm").reset();
    document.getElementById("businessId").value = "";
    businessModal.show();
};


async function editBusiness(id) {
    const res = await fetch(`/businesses/${id}/edit`);
    const data = await res.json();
    if (data.success) {
        const b = data.data;
        document.getElementById("businessId").value = id;
        document.querySelector("[name=\"name\"]").value = b.name;
        document.querySelector("[name=\"owner_name\"]").value = b.owner_name;
        document.querySelector("[name=\"business_type\"]").value = b.business_type || "";
        document.querySelector("[name=\"address\"]").value = b.address || "";
        document.querySelector("[name=\"status\"]").value = b.status;
        businessModal.show();
    }
}

async function deleteBusiness(id) {
    if (!confirm("Are you sure you want to delete this business record?")) return;
    try {
        const res = await fetch(`/businesses/${id}`, {
            method: "DELETE",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" }
        });
        if (res.ok) {
            alert("Business deleted successfully");
            location.reload();
        } else {
            alert("Failed to delete record.");
        }
    } catch (err) {
        console.error(err);
    }
}

document.getElementById("businessForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("businessId").value;
    const url = id ? `/businesses/${id}` : "/businesses";
    const method = id ? "PUT" : "POST";

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            alert("Business record saved successfully");
            businessModal.hide();
            location.reload();
        } else {
            const errorData = await res.json();
            alert("Error: " + JSON.stringify(errorData.errors || errorData.message));
        }
    } catch (err) {
        alert("An error occurred: " + err.message);
    }
});
</script>
@endpush
