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

<div id="businessAlert" class="mb-3"></div>

<div class="table-card">
    <table class="table table-hover" id="businessesTable">
        <thead>
            <tr>
                <th>Business Name</th>
                <th>Owner</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
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
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Owner Name</label>
                        <input type="text" class="form-control" name="owner_name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        @php
                            $types = ['Supermarket', 'Laundry Service', 'Pharmacy', 'Restaurant', 'Retail Service', 'Sari-Sari'];
                        @endphp
                        <label class="form-label fw-600">Business Type</label>
                        <select name="business_type" class="form-select">
                            @foreach ($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Address</label>
                        <textarea class="form-control" name="business_address" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Date Established</label>
                        <input type="date" class="form-control" name="date_established" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Status</label>
                        <select class="form-select" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Closed">Closed</option>
                        </select>
                        <div class="invalid-feedback"></div>
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

@section("scripts")
<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.2/datatables.min.css">
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.2/datatables.min.js"></script>
<script>
const businessModal = new bootstrap.Modal(document.getElementById("businessModal"));
const businessForm = document.getElementById("businessForm");
const alertContainer = document.getElementById("businessAlert");
let businessesTable = null;

const axiosInstance = window.axios;
if (axiosInstance) {
    axiosInstance.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
    axiosInstance.defaults.headers.common["Accept"] = "application/json";
    const csrfToken = document.querySelector("meta[name=\"csrf-token\"]")?.getAttribute("content");
    if (csrfToken) {
        axiosInstance.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
    }
}

function showAlert(message, type = "success") {
    if (!alertContainer) return;
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function clearValidation() {
    businessForm.querySelectorAll(".is-invalid").forEach((el) => el.classList.remove("is-invalid"));
    businessForm.querySelectorAll(".invalid-feedback").forEach((el) => {
        el.textContent = "";
    });
}

function showFormErrors(errors) {
    Object.keys(errors).forEach((field) => {
        const input = businessForm.querySelector(`[name="${field}"]`);
        if (!input) return;
        input.classList.add("is-invalid");
        const feedback = input.parentElement.querySelector(".invalid-feedback");
        if (feedback) {
            feedback.textContent = errors[field][0];
        }
    });
}

window.openCreateModal = () => {
    businessForm.reset();
    clearValidation();
    document.getElementById("businessId").value = "";
    businessModal.show();
};


async function editBusiness(id) {
    if (!axiosInstance) return;
    clearValidation();
    try {
        const response = await axiosInstance.get(`/businesses/${id}/edit`);
        const data = response.data?.data || response.data;
        document.getElementById("businessId").value = id;
        document.querySelector("[name=\"business_name\"]").value = data.business_name || "";
        document.querySelector("[name=\"owner_name\"]").value = data.owner_name || "";
        document.querySelector("[name=\"business_type\"]").value = data.business_type || "";
        document.querySelector("[name=\"business_address\"]").value = data.business_address || "";
        document.querySelector("[name=\"date_established\"]").value = data.date_established || "";
        document.querySelector("[name=\"status\"]").value = data.status || "Active";
        businessModal.show();
    } catch (error) {
        showAlert("Failed to load business details.", "danger");
    }
}

async function deleteBusiness(id) {
    if (!axiosInstance) return;
    if (!confirm("Are you sure you want to delete this business record?")) return;
    try {
        const response = await axiosInstance.delete(`/businesses/${id}`);
        showAlert(response.data?.message || "Business deleted successfully.");
        if (businessesTable) {
            businessesTable.ajax.reload(null, false);
        }
    } catch (err) {
        showAlert(err.response?.data?.message || "Failed to delete record.", "danger");
    }
}

businessForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearValidation();

    const id = document.getElementById("businessId").value;
    const url = id ? `/businesses/${id}` : "/businesses";
    const method = id ? "put" : "post";
    const formData = new FormData(businessForm);
    const data = Object.fromEntries(formData);

    try {
        const response = await axiosInstance({ method, url, data });
        showAlert(response.data?.message || "Business record saved successfully.");
        businessModal.hide();
        businessForm.reset();
        if (businessesTable) {
            businessesTable.ajax.reload(null, false);
        }
    } catch (error) {
        if (error.response?.status === 422) {
            showFormErrors(error.response.data.errors || {});
            return;
        }
        showAlert(error.response?.data?.message || "An error occurred while saving.", "danger");
    }
});

document.addEventListener("DOMContentLoaded", () => {
    if (window.$ && $.fn.DataTable) {
        businessesTable = $("#businessesTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('businesses.data') }}",
                dataSrc: "data",
                headers: { "Accept": "application/json" }
            },
            columns: [
                { data: "business_name", name: "business_name" },
                { data: "owner_names", name: "owner_names", orderable: false, searchable: false },
                { data: "business_type", name: "business_type" },
                { data: "status_badge", name: "status", orderable: false, searchable: false },
                { data: "action", name: "action", orderable: false, searchable: false }
            ],
            order: [[0, "asc"]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            responsive: true,
            language: {
                search: "Search businesses:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ businesses",
                infoEmpty: "No businesses available",
                infoFiltered: "(filtered from _MAX_ total businesses)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }

    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-business-action]");
        if (!button) return;

        const { businessAction, businessId } = button.dataset;

        if (businessAction === "edit") {
            editBusiness(businessId);
            return;
        }

        if (businessAction === "delete") {
            deleteBusiness(businessId);
        }
    });
});
</script>
@endsection
