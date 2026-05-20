@extends("layouts.app")

@section("title", "Business Permits - Barangay Management System")
@section("page-title", "Business Permits")

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Business Permit Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Register businesses, issue permits, and track annual renewals.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
            style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
        <i class="bi bi-plus-lg"></i> Add Business
    </button>
</div>

<div id="businessAlert" class="mb-3"></div>

<div class="table-card">
    <table class="table table-hover align-middle" id="businessesTable">
        <thead>
            <tr>
                <th>Business Name</th>
                <th>Owner</th>
                <th>Type</th>
                <th>Business Status</th>
                <th>Permit No.</th>
                <th>Permit Status</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="businessModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Business Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="businessForm">
                @csrf
                <input type="hidden" id="businessId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Business Name</label>
                            <input type="text" class="form-control" name="business_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            @php
                                $types = ['Supermarket', 'Laundry Service', 'Pharmacy', 'Restaurant', 'Retail Service', 'Sari-Sari'];
                            @endphp
                            <label class="form-label fw-600">Business Type</label>
                            <select name="business_type" class="form-select" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Address</label>
                            <textarea class="form-control" name="business_address" rows="2" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Date Established</label>
                            <input type="date" class="form-control" name="date_established" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Closed">Closed</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-600 mb-0">Business Owners</label>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addOwnerRow()">
                                <i class="bi bi-plus-lg"></i> Add Owner
                            </button>
                        </div>
                        <div id="ownersContainer"></div>
                        <div class="invalid-feedback d-block" id="ownersError"></div>
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

<div class="modal fade" id="issuePermitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Issue Business Permit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="issuePermitForm">
                @csrf
                <input type="hidden" name="business_id" id="issueBusinessId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Issue Date</label>
                        <input type="date" class="form-control" name="issued_date" id="issuedDate">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" id="issueExpiryDate">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-receipt"></i> Issue Permit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="renewPermitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Renew Business Permit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="renewPermitForm">
                @csrf
                <input type="hidden" name="business_id" id="renewBusinessId">
                <input type="hidden" name="permit_id" id="renewPermitId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Renewal Date</label>
                        <input type="date" class="form-control" name="renewal_date" id="renewalDate">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">New Expiry Date</label>
                        <input type="date" class="form-control" name="new_expiry_date" id="renewalExpiryDate">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Fee Paid</label>
                        <input type="number" class="form-control" name="fee_paid" min="0" step="0.01" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Renew Permit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="permitHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800" id="permitHistoryTitle">Permit History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="permitHistoryBody"></div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.2/datatables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.2/datatables.min.js"></script>

<script>
@php
    $availableOwners = \App\Models\BusinessOwner::with('resident')
        ->get()
        ->map(function ($owner) {
            if ($owner->resident_id && $owner->resident) {
                $name = trim($owner->resident->first_name . ' ' . ($owner->resident->middle_name ?? '') . ' ' . $owner->resident->last_name . ' ' . ($owner->resident->suffix ?? ''));
            } else {
                $name = $owner->organization_name ?: trim(($owner->first_name ?? '') . ' ' . ($owner->middle_name ?? '') . ' ' . ($owner->last_name ?? '') . ' ' . ($owner->suffix ?? ''));
            }

            return [
                'id' => $owner->id,
                'name' => trim(preg_replace('/\s+/', ' ', $name)),
            ];
        })
        ->values();
@endphp

const availableOwners = @json($availableOwners);
const businessModal = new bootstrap.Modal(document.getElementById("businessModal"));
const issuePermitModal = new bootstrap.Modal(document.getElementById("issuePermitModal"));
const renewPermitModal = new bootstrap.Modal(document.getElementById("renewPermitModal"));
const permitHistoryModal = new bootstrap.Modal(document.getElementById("permitHistoryModal"));
const businessForm = document.getElementById("businessForm");
const issuePermitForm = document.getElementById("issuePermitForm");
const renewPermitForm = document.getElementById("renewPermitForm");
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

function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, (char) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        "\"": "&quot;",
        "'": "&#039;"
    }[char]));
}

function todayString() {
    return new Date().toISOString().slice(0, 10);
}

function annualExpiry(dateValue) {
    const date = new Date(`${dateValue}T00:00:00`);
    date.setFullYear(date.getFullYear() + 1);
    date.setDate(date.getDate() - 1);
    return date.toISOString().slice(0, 10);
}

function showAlert(message, type = "success") {
    if (!alertContainer) return;
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function clearValidation(form) {
    form.querySelectorAll(".is-invalid").forEach((el) => el.classList.remove("is-invalid"));
    form.querySelectorAll(".invalid-feedback").forEach((el) => {
        el.textContent = "";
    });
    document.getElementById("ownersError").textContent = "";
}

function showFormErrors(form, errors) {
    Object.keys(errors).forEach((field) => {
        const input = form.querySelector(`[name="${field}"]`) || form.querySelector(`[name="${field}[]"]`);
        const message = errors[field][0];

        if (!input && field === "business_owner_ids") {
            document.getElementById("ownersError").textContent = message;
            return;
        }

        if (!input) return;
        input.classList.add("is-invalid");
        const feedback = input.closest(".mb-3, .col-md-6, .col-12, .col-md-5, .col-md-3")?.querySelector(".invalid-feedback");
        if (feedback) {
            feedback.textContent = message;
        }
    });
}

function reloadBusinesses() {
    if (businessesTable?.ajax) {
        businessesTable.ajax.reload(null, false);
    }
}

function ownerOptions(selectedId = "") {
    return availableOwners.map(owner => `
        <option value="${escapeHtml(owner.id)}" ${selectedId == owner.id ? "selected" : ""}>
            ${escapeHtml(owner.name)}
        </option>
    `).join("");
}

function addOwnerRow(data = {}) {
    const container = document.getElementById("ownersContainer");
    const row = document.createElement("div");

    row.classList.add("border", "rounded", "p-3", "mb-3", "owner-row");
    row.innerHTML = `
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Owner</label>
                <select class="form-select" name="business_owner_ids[]" required>
                    <option value="">Select Owner</option>
                    ${ownerOptions(data.business_owner_id)}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select class="form-select" name="ownership_roles[]">
                    <option value="Owner" ${data.ownership_role === "Owner" ? "selected" : ""}>Owner</option>
                    <option value="Co-owner" ${data.ownership_role === "Co-owner" ? "selected" : ""}>Co-owner</option>
                    <option value="Representative" ${data.ownership_role === "Representative" ? "selected" : ""}>Representative</option>
                </select>
            </div>
            <div class="col-md-3 ownership-col">
                <label class="form-label">Ownership %</label>
                <input type="number" class="form-control ownership-input" name="ownership_percentages[]" min="0" max="100" step="0.01" value="${escapeHtml(data.ownership_percentage ?? "")}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger w-100" onclick="this.closest('.owner-row').remove();toggleOwnershipFields();">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;

    container.appendChild(row);
    toggleOwnershipFields();
}

function toggleOwnershipFields() {
    const rows = document.querySelectorAll(".owner-row");
    const showPercentage = rows.length > 1;

    rows.forEach(row => {
        const input = row.querySelector(".ownership-input");
        row.querySelector(".ownership-col").style.display = showPercentage ? "block" : "none";
        if (!showPercentage) {
            input.value = "";
        }
    });
}

window.openCreateModal = () => {
    businessForm.reset();
    clearValidation(businessForm);
    document.getElementById("businessId").value = "";
    document.getElementById("ownersContainer").innerHTML = "";
    addOwnerRow();
    businessModal.show();
};

async function editBusiness(id) {
    clearValidation(businessForm);

    try {
        const response = await axiosInstance.get(`/businesses/${id}/edit`);
        const data = response.data?.data || response.data;

        document.getElementById("businessId").value = id;
        businessForm.querySelector("[name=\"business_name\"]").value = data.business_name || "";
        businessForm.querySelector("[name=\"business_type\"]").value = data.business_type || "";
        businessForm.querySelector("[name=\"business_address\"]").value = data.business_address || "";
        businessForm.querySelector("[name=\"date_established\"]").value = data.date_established || "";
        businessForm.querySelector("[name=\"status\"]").value = data.status || "Active";
        document.getElementById("ownersContainer").innerHTML = "";
        (data.owners || []).forEach(owner => addOwnerRow(owner));
        if (!data.owners?.length) addOwnerRow();
        businessModal.show();
    } catch (error) {
        showAlert("Failed to load business details.", "danger");
    }
}

async function deleteBusiness(id) {
    if (!confirm("Are you sure you want to delete this business record?")) return;

    try {
        const response = await axiosInstance.delete(`/businesses/${id}`);
        showAlert(response.data?.message || "Business deleted successfully.");
        reloadBusinesses();
    } catch (error) {
        showAlert(error.response?.data?.message || "Failed to delete record.", "danger");
    }
}

function openIssuePermitModal(id) {
    issuePermitForm.reset();
    clearValidation(issuePermitForm);
    document.getElementById("issueBusinessId").value = id;
    document.getElementById("issuedDate").value = todayString();
    document.getElementById("issueExpiryDate").value = annualExpiry(todayString());
    issuePermitModal.show();
}

function openRenewPermitModal(businessId, permitId) {
    renewPermitForm.reset();
    clearValidation(renewPermitForm);
    document.getElementById("renewBusinessId").value = businessId;
    document.getElementById("renewPermitId").value = permitId;
    document.getElementById("renewalDate").value = todayString();
    document.getElementById("renewalExpiryDate").value = annualExpiry(todayString());
    renewPermitModal.show();
}

async function openPermitHistory(id) {
    const title = document.getElementById("permitHistoryTitle");
    const body = document.getElementById("permitHistoryBody");
    title.textContent = "Permit History";
    body.innerHTML = "<p class=\"text-muted mb-0\">Loading permit history...</p>";
    permitHistoryModal.show();

    try {
        const response = await axiosInstance.get(`/businesses/${id}/permits/history`);
        const data = response.data;
        title.textContent = `${data.business.business_name} Permit History`;

        if (!data.permits.length) {
            body.innerHTML = "<p class=\"text-muted mb-0\">No permits have been issued for this business.</p>";
            return;
        }

        body.innerHTML = data.permits.map(permit => `
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between gap-3 flex-wrap">
                    <div>
                        <div class="fw-700">${escapeHtml(permit.permit_number)}</div>
                        <div class="text-muted" style="font-size:13px;">Issued ${escapeHtml(permit.issued_date || "N/A")} by ${escapeHtml(permit.issued_by)}</div>
                    </div>
                    <span class="badge bg-light text-dark align-self-start">${escapeHtml(permit.display_status)}</span>
                </div>
                <div class="mt-2" style="font-size:13px;">Expiry: <strong>${escapeHtml(permit.expiry_date || "N/A")}</strong></div>
                <div class="mt-3">
                    <div class="fw-600 mb-2" style="font-size:13px;">Renewals</div>
                    ${permit.renewals.length ? permit.renewals.map(renewal => `
                        <div class="d-flex justify-content-between border-top py-2" style="font-size:13px;">
                            <span>${escapeHtml(renewal.renewal_date)} to ${escapeHtml(renewal.new_expiry_date || "N/A")}</span>
                            <span>PHP ${escapeHtml(renewal.fee_paid)}</span>
                        </div>
                    `).join("") : "<div class=\"text-muted\" style=\"font-size:13px;\">No renewals yet.</div>"}
                </div>
            </div>
        `).join("");
    } catch (error) {
        body.innerHTML = "<p class=\"text-danger mb-0\">Failed to load permit history.</p>";
    }
}

businessForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    clearValidation(businessForm);

    const id = document.getElementById("businessId").value;
    const formData = new FormData(businessForm);
    if (id) {
        formData.append("_method", "PUT");
    }

    try {
        const response = await axiosInstance.post(id ? `/businesses/${id}` : "/businesses", formData);
        showAlert(response.data?.message || "Business record saved successfully.");
        businessModal.hide();
        businessForm.reset();
        reloadBusinesses();
    } catch (error) {
        if (error.response?.status === 422) {
            showFormErrors(businessForm, error.response.data.errors || {});
            showAlert("Please check the business form fields.", "danger");
            return;
        }
        showAlert(error.response?.data?.message || "An error occurred while saving.", "danger");
    }
});

issuePermitForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    clearValidation(issuePermitForm);

    const id = document.getElementById("issueBusinessId").value;

    try {
        const response = await axiosInstance.post(`/businesses/${id}/permits`, new FormData(issuePermitForm));
        showAlert(response.data?.message || "Business permit issued successfully.");
        issuePermitModal.hide();
        reloadBusinesses();
    } catch (error) {
        if (error.response?.status === 422) {
            showFormErrors(issuePermitForm, error.response.data.errors || {});
            showAlert(error.response.data.message || "Please check the permit form fields.", "danger");
            return;
        }
        showAlert(error.response?.data?.message || "Failed to issue permit.", "danger");
    }
});

renewPermitForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    clearValidation(renewPermitForm);

    const businessId = document.getElementById("renewBusinessId").value;
    const permitId = document.getElementById("renewPermitId").value;

    try {
        const response = await axiosInstance.post(`/businesses/${businessId}/permits/${permitId}/renew`, new FormData(renewPermitForm));
        showAlert(response.data?.message || "Business permit renewed successfully.");
        renewPermitModal.hide();
        reloadBusinesses();
    } catch (error) {
        if (error.response?.status === 422) {
            showFormErrors(renewPermitForm, error.response.data.errors || {});
            showAlert(error.response.data.message || "Please check the renewal form fields.", "danger");
            return;
        }
        showAlert(error.response?.data?.message || "Failed to renew permit.", "danger");
    }
});

document.getElementById("issuedDate").addEventListener("change", (event) => {
    document.getElementById("issueExpiryDate").value = annualExpiry(event.target.value || todayString());
});

document.getElementById("renewalDate").addEventListener("change", (event) => {
    document.getElementById("renewalExpiryDate").value = annualExpiry(event.target.value || todayString());
});

document.addEventListener("DOMContentLoaded", () => {
    if (typeof $ !== "undefined" && $.fn.DataTable) {
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
                { data: "permit_number", name: "permit_number", orderable: false, searchable: false },
                { data: "permit_status_badge", name: "permit_status", orderable: false, searchable: false },
                { data: "expiry_date", name: "expiry_date", orderable: false, searchable: false },
                { data: "action", name: "action", orderable: false, searchable: false }
            ],
            order: [[0, "asc"]],
            pageLength: 10,
            responsive: true
        });
    }

    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-business-action]");
        if (!button) return;

        const { businessAction, businessId, permitId } = button.dataset;

        if (businessAction === "edit") editBusiness(businessId);
        if (businessAction === "delete") deleteBusiness(businessId);
        if (businessAction === "issue") openIssuePermitModal(businessId);
        if (businessAction === "renew") openRenewPermitModal(businessId, permitId);
        if (businessAction === "history") openPermitHistory(businessId);
    });
});
</script>
@endsection
