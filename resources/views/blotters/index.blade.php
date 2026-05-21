@extends("layouts.app")

@section("title", "Blotter Management - Barangay Management System")
@section("page-title", "Blotter Management")

@section("styles")
<style>
    .blotter-dashboard .stat-card { min-height: 104px; }
    .blotter-dashboard .stat-value { font-size: 30px; }
    .blotter-chart { height: 230px; }
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        min-height: 164px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #94a3b8;
        cursor: pointer;
        transition: all .18s ease;
    }
    .upload-zone:hover,
    .upload-zone.dragover {
        border-color: #1a56db;
        background: #eff6ff;
        color: #1a56db;
    }
    .timeline {
        position: relative;
        padding-left: 28px;
    }
    .timeline:before {
        content: "";
        position: absolute;
        left: 8px;
        top: 9px;
        bottom: 11px;
        width: 2px;
        background: #dbe4f0;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 24px;
    }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-dot {
        position: absolute;
        left: -27px;
        top: 4px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #1a56db;
    }
    .timeline-dot.success { border-color: #16a34a; }
    .timeline-dot.warning { border-color: #f59e0b; }
    .timeline-dot.secondary { border-color: #94a3b8; }
    .dataTables_wrapper .dt-search,
    .dataTables_wrapper .dt-length {
        padding: 12px 18px;
    }
    .dataTables_wrapper .dt-info,
    .dataTables_wrapper .dt-paging {
        padding: 14px 18px;
    }
    .party-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px;
        background: #f8fafc;
    }
    .resident-results {
        position: absolute;
        z-index: 1060;
        right: 0;
        left: 0;
        max-height: 210px;
        overflow-y: auto;
        border: 1px solid #dbe4f0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .14);
    }
    .resident-results option {
        padding: 9px 12px;
        font-size: 13px;
    }
    #blotterModal .modal-dialog {
        height: calc(100vh - 2rem);
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    #blotterModal .modal-content {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    #blotterForm {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
    }
    #blotterModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }
</style>
@endsection

@section("content")
<div class="blotter-dashboard">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-800 mb-1" style="font-size:20px;">Blotter Management</h5>
            <p class="mb-0" style="font-size:13px;color:#64748b;">Log, track, and resolve barangay complaints and incidents.</p>
        </div>

        <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
                style="border-radius:8px;font-size:13.5px;font-weight:700;padding:11px 20px;">
            <i class="bi bi-plus-lg"></i>
            Add New Blotter Record
        </button>
    </div>

    <div id="blotterAlert" class="mb-3"></div>

    <div class="section-heading">Overview</div>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-journal-text"></i></div>
                <div>
                    <div class="stat-value" data-summary="total">{{ $summary['total'] }}</div>
                    <div class="stat-label">Total Complaints Logged</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-exclamation-circle-fill"></i></div>
                <div>
                    <div class="stat-value" data-summary="open">{{ $summary['open'] }}</div>
                    <div class="stat-label">Open Cases</div>
                    <span class="stat-badge" style="background:#fee2e2;color:#dc2626;">Needs action</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fffbeb;color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-value" data-summary="ongoing">{{ $summary['ongoing'] }}</div>
                    <div class="stat-label">Ongoing / Mediation</div>
                    <span class="stat-badge" style="background:#fef3c7;color:#d97706;">In progress</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#ecfdf5;color:#16a34a;"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="stat-value" data-summary="resolved">{{ $summary['resolved'] }}</div>
                    <div class="stat-label">Resolved Cases</div>
                    <span class="stat-badge" style="background:#dcfce7;color:#16a34a;" data-summary="resolved_rate">{{ $summary['resolved_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="card-heading">Case Status Distribution</div>
                <div class="card-sub mb-3">All logged complaints</div>
                <div class="blotter-chart"><canvas id="statusChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="card-heading">Complaint Type Breakdown</div>
                <div class="card-sub mb-3">This year</div>
                <div class="blotter-chart"><canvas id="typeChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="card-heading">Attach Supporting Documents</div>
                <div class="card-sub mb-4">Upload files for an existing blotter record</div>
                <form id="evidenceForm">
                    <label class="form-label fw-700">Blotter Reference No.</label>
                    <select class="form-select mb-3" name="reference" id="evidenceReference" required>
                        <option value="">Select blotter reference</option>
                        @foreach($blotterReferences as $reference)
                            <option value="{{ $reference['case_number'] }}">{{ $reference['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="file" class="d-none" id="evidenceFile" name="evidence" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.mp4,.mov,.avi,.webm,.mkv,.mpeg,.mpg,image/jpeg,image/png,image/webp,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska,video/mpeg" required>
                    <div class="upload-zone mb-3" id="uploadZone">
                        <div>
                            <i class="bi bi-cloud-arrow-up-fill d-block mb-3" style="font-size:24px;"></i>
                            <div class="fw-700" id="uploadLabel">Drop files here or click to browse</div>
                            <div style="font-size:12px;">PDF, DOC, images, videos - max 100MB</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100 fw-700">
                        <i class="bi bi-paperclip me-1"></i>
                        Attach to Record
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="section-heading">Recent Incidents</div>
            <div class="table-card">
                <div class="table-header">
                    <div class="heading">Incident Log</div>
                    <a href="{{ route('blotters.export') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>Export
                    </a>
                </div>
                <table class="table table-hover" id="blottersTable">
                    <thead>
                        <tr>
                            <th>Ref. No.</th>
                            <th>Complainant</th>
                            <th>Incident Type</th>
                            <th>Date Filed</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="section-heading">Case Status Tracker</div>
            <div class="chart-card">
                <div class="timeline">
                    @forelse($recentTracker as $item)
                        <div class="timeline-item">
                            <span class="timeline-dot {{ $item['tone'] }}"></span>
                            <div class="small fw-600 mb-1" style="color:#94a3b8;">{{ $item['date'] }}</div>
                            <div class="fw-800" style="font-size:14px;">{{ $item['case_number'] }} {{ $item['status'] }}</div>
                            <div style="font-size:13px;color:#64748b;">{{ $item['description'] }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">No tracked cases yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="blotterModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Blotter Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="blotterForm" method="POST" action="{{ route('blotters.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="blotterId" name="id">
                <div class="modal-body">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-journal-plus text-primary"></i>
                                <h6 class="fw-800 mb-0">Incident Details</h6>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-600">Case Number</label>
                                    <input type="text" class="form-control" id="caseNumberDisplay" value="Auto-generated on save" readonly>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-600">Incident Title</label>
                                    <input type="text" class="form-control" name="incident_title" placeholder="Short case title">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-600">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="pending">Open</option>
                                        <option value="under investigation">Ongoing / Mediation</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="dismissed">Dismissed</option>
                                        <option value="referred">Referred</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-600">Incident Date and Time</label>
                                    <input type="datetime-local" class="form-control" name="incident_date" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-600">Incident Location</label>
                                    <input type="text" class="form-control" name="location">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label fw-600">Incident Description</label>
                                <textarea class="form-control" name="incident_description" rows="3" required></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-person-vcard text-primary"></i>
                                <h6 class="fw-800 mb-0">Complainant</h6>
                            </div>
                            <div class="party-card" data-person-section="complainant" data-index="0">
                                <div class="d-flex gap-3 flex-wrap mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input person-mode" type="radio" name="complainant_mode" value="resident" id="complainantResident">
                                        <label class="form-check-label fw-600" for="complainantResident">Registered resident</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input person-mode" type="radio" name="complainant_mode" value="manual" id="complainantManual" checked>
                                        <label class="form-check-label fw-600" for="complainantManual">Non-resident</label>
                                    </div>
                                </div>
                                <input type="hidden" name="complainant_id" data-resident-id>
                                <div class="resident-field d-none position-relative">
                                    <label class="form-label fw-600">Search Resident</label>
                                    <input type="text" class="form-control resident-search" placeholder="Type a resident name or number">
                                    <select class="form-select resident-results d-none" size="5"></select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="manual-field">
                                    <label class="form-label fw-600">Complainant Name</label>
                                    <input type="text" class="form-control" name="complainant_name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-people text-primary"></i>
                                    <h6 class="fw-800 mb-0">Respondents</h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-700" id="addRespondentBtn">
                                    <i class="bi bi-plus-lg me-1"></i>Add Respondent
                                </button>
                            </div>
                            <div id="respondentsContainer" class="d-grid gap-3"></div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-lines-fill text-primary"></i>
                                    <h6 class="fw-800 mb-0">Witnesses</h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-700" id="addWitnessBtn">
                                    <i class="bi bi-plus-lg me-1"></i>Add Witness
                                </button>
                            </div>
                            <div id="witnessesContainer" class="d-grid gap-3"></div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-paperclip text-primary"></i>
                                <h6 class="fw-800 mb-0">Evidence Uploads</h6>
                            </div>
                            <input type="file" class="form-control" name="evidences[]" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.mp4,.mov,.avi,.webm,.mkv,.mpeg,.mpg,image/jpeg,image/png,image/webp,image/gif,application/pdf,video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska,video/mpeg" multiple>
                            <div class="form-text">Up to 5 files. Allowed: images, PDF, and common video formats. Maximum 100 MB per file.</div>
                            <div class="form-text">Optional. Attach images, videos, PDFs, or documents up to 50MB each.</div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <template id="partyTemplate">
                        <div class="party-card" data-person-section data-index>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                <div class="fw-800 party-title"></div>
                                <button type="button" class="btn btn-sm btn-light text-danger remove-party" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="d-flex gap-3 flex-wrap mb-3">
                                <div class="form-check">
                                    <input class="form-check-input person-mode" type="radio" value="resident">
                                    <label class="form-check-label fw-600">Registered resident</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input person-mode" type="radio" value="manual" checked>
                                    <label class="form-check-label fw-600">Non-resident</label>
                                </div>
                            </div>
                            <input type="hidden" data-resident-id>
                            <div class="resident-field d-none position-relative">
                                <label class="form-label fw-600">Search Resident</label>
                                <input type="text" class="form-control resident-search" placeholder="Type a resident name or number">
                                <select class="form-select resident-results d-none" size="5"></select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="manual-field">
                                <label class="form-label fw-600 manual-label"></label>
                                <input type="text" class="form-control manual-name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Blotter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
const blotterModal = new bootstrap.Modal(document.getElementById("blotterModal"));
const blotterForm = document.getElementById("blotterForm");
const evidenceForm = document.getElementById("evidenceForm");
const evidenceReference = document.getElementById("evidenceReference");
const uploadZone = document.getElementById("uploadZone");
const evidenceFile = document.getElementById("evidenceFile");
const uploadLabel = document.getElementById("uploadLabel");
const alertContainer = document.getElementById("blotterAlert");
const axiosInstance = window.axios;
let blottersTable = null;
let statusChart = null;
let typeChart = null;

const statusData = @json($statusDistribution);
const typeData = @json($typeBreakdown);
let blotterReferences = @json($blotterReferences);

if (axiosInstance) {
    axiosInstance.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
    axiosInstance.defaults.headers.common["Accept"] = "application/json";
    const csrfToken = document.querySelector("meta[name=\"csrf-token\"]")?.getAttribute("content");
    if (csrfToken) {
        axiosInstance.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
    }
}

function showAlert(message, type = "success") {
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function clearValidation() {
    blotterForm.querySelectorAll(".is-invalid").forEach((el) => el.classList.remove("is-invalid"));
    blotterForm.querySelectorAll(".invalid-feedback").forEach((el) => el.textContent = "");
}

function showFormErrors(errors) {
    let firstInvalidInput = null;

    Object.keys(errors).forEach((field) => {
        const input = blotterForm.querySelector(`[name="${field}"]`);
        if (!input) {
            showAlert(errors[field][0], "danger");
            return;
        }
        input.classList.add("is-invalid");
        firstInvalidInput ??= input;
        const feedback = input.parentElement.querySelector(".invalid-feedback");
        if (feedback) feedback.textContent = errors[field][0];
    });

    firstInvalidInput?.scrollIntoView({ behavior: "smooth", block: "center" });
}

function updateSummary(summary) {
    if (!summary) return;
    Object.entries(summary).forEach(([key, value]) => {
        document.querySelectorAll(`[data-summary="${key}"]`).forEach((el) => {
            el.textContent = key === "resolved_rate" ? `${value}%` : value;
        });
    });
}

function requestJson(url, options = {}) {
    if (axiosInstance) {
        const method = options.method || "get";
        return axiosInstance({
            method,
            url,
            data: options.data,
            headers: options.headers || {},
        }).then((response) => response.data);
    }

    const headers = {
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(options.headers || {}),
    };
    const csrfToken = document.querySelector("meta[name=\"csrf-token\"]")?.getAttribute("content");
    if (csrfToken) headers["X-CSRF-TOKEN"] = csrfToken;

    return fetch(url, {
        method: options.method || "GET",
        body: options.data,
        headers,
    }).then(async (response) => {
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const error = new Error(payload.message || "Request failed.");
            error.response = { status: response.status, data: payload };
            throw error;
        }
        return payload;
    });
}

function updateEvidenceReferences(references) {
    if (!Array.isArray(references)) return;
    const currentValue = evidenceReference.value;
    blotterReferences = references;
    evidenceReference.innerHTML = '<option value="">Select blotter reference</option>';
    references.forEach((reference) => {
        const option = new Option(reference.label, reference.case_number);
        evidenceReference.appendChild(option);
    });
    if (references.some((reference) => reference.case_number === currentValue)) {
        evidenceReference.value = currentValue;
    }
}

function updateDashboard(payload) {
    updateSummary(payload?.summary);
    updateEvidenceReferences(payload?.blotterReferences);
}

function resetParties() {
    document.getElementById("respondentsContainer").innerHTML = "";
    document.getElementById("witnessesContainer").innerHTML = "";
    setComplainantMode("manual");
    addParty("respondent");
}

function setComplainantMode(mode, resident = null, name = "") {
    const section = blotterForm.querySelector("[data-person-section=\"complainant\"]");
    const residentRadio = section.querySelector("[value=\"resident\"]");
    const manualRadio = section.querySelector("[value=\"manual\"]");
    residentRadio.checked = mode === "resident";
    manualRadio.checked = mode !== "resident";
    section.querySelector("[data-resident-id]").value = mode === "resident" ? (resident?.id || "") : "";
    section.querySelector(".resident-search").value = mode === "resident" ? (resident?.text || name || "") : "";
    section.querySelector("[name=\"complainant_name\"]").value = mode === "resident" ? "" : name;
    togglePersonMode(section);
}

function partyContainerId(type) {
    return type === "witness" ? "witnessesContainer" : `${type}sContainer`;
}

function partyFieldName(type) {
    return type === "witness" ? "witnesses" : `${type}s`;
}

function addParty(type, person = {}) {
    const container = document.getElementById(partyContainerId(type));
    const index = container.children.length;
    const node = document.getElementById("partyTemplate").content.firstElementChild.cloneNode(true);
    const label = type === "respondent" ? "Respondent" : "Witness";
    const mode = person.resident_id ? "resident" : "manual";

    node.dataset.personSection = type;
    node.dataset.index = index;
    node.querySelector(".party-title").textContent = `${label} #${index + 1}`;
    node.querySelector(".manual-label").textContent = `${label} Name`;
    node.querySelectorAll(".person-mode").forEach((input) => {
        input.name = `${partyFieldName(type)}[${index}][mode]`;
        input.checked = input.value === mode;
    });
    node.querySelector("[data-resident-id]").name = `${partyFieldName(type)}[${index}][resident_id]`;
    node.querySelector("[data-resident-id]").value = person.resident_id || "";
    node.querySelector(".manual-name").name = `${partyFieldName(type)}[${index}][name]`;
    node.querySelector(".manual-name").value = person.resident_id ? "" : (person.name || "");
    node.querySelector(".resident-search").value = person.resident_id ? (person.name || "") : "";
    container.appendChild(node);
    togglePersonMode(node);
}

function reindexParties(type) {
    document.querySelectorAll(`#${partyContainerId(type)} .party-card`).forEach((node, index) => {
        const label = type === "respondent" ? "Respondent" : "Witness";
        node.dataset.index = index;
        node.querySelector(".party-title").textContent = `${label} #${index + 1}`;
        node.querySelectorAll(".person-mode").forEach((input) => input.name = `${partyFieldName(type)}[${index}][mode]`);
        node.querySelector("[data-resident-id]").name = `${partyFieldName(type)}[${index}][resident_id]`;
        node.querySelector(".manual-name").name = `${partyFieldName(type)}[${index}][name]`;
    });
}

function togglePersonMode(section) {
    const mode = section.querySelector(".person-mode:checked")?.value || "manual";
    const isResident = mode === "resident";
    const residentField = section.querySelector(".resident-field");
    const manualField = section.querySelector(".manual-field");
    const residentSearch = section.querySelector(".resident-search");
    const manualInput = section.querySelector(".manual-name, [name=\"complainant_name\"]");

    residentField?.classList.toggle("d-none", !isResident);
    manualField?.classList.toggle("d-none", isResident);

    if (residentSearch) {
        residentSearch.disabled = !isResident;
        residentSearch.required = isResident;
    }

    if (manualInput) {
        manualInput.disabled = isResident;
        manualInput.required = !isResident;
    }

    if (!isResident) {
        section.querySelector("[data-resident-id]").value = "";
        if (residentSearch) residentSearch.value = "";
        section.querySelector(".resident-results")?.classList.add("d-none");
    } else {
        if (manualInput) manualInput.value = "";
    }
}

function validateResidentSelections() {
    let isValid = true;

    blotterForm.querySelectorAll(".party-card").forEach((section) => {
        const mode = section.querySelector(".person-mode:checked")?.value || "manual";
        const residentId = section.querySelector("[data-resident-id]")?.value;
        const searchInput = section.querySelector(".resident-search");
        const manualInput = section.querySelector(".manual-name, [name=\"complainant_name\"]");

        if (mode === "resident" && !residentId && searchInput) {
            searchInput.classList.add("is-invalid");
            const feedback = searchInput.parentElement.querySelector(".invalid-feedback");
            if (feedback) feedback.textContent = "Select a resident from the search results.";
            isValid = false;
        }

        if (mode === "manual" && manualInput && !manualInput.value.trim()) {
            manualInput.classList.add("is-invalid");
            const feedback = manualInput.parentElement.querySelector(".invalid-feedback");
            if (feedback) feedback.textContent = "Enter the non-resident name.";
            isValid = false;
        }
    });

    return isValid;
}

async function searchResidents(input) {
    const query = input.value.trim();
    const section = input.closest(".party-card");
    const results = section.querySelector(".resident-results");
    section.querySelector("[data-resident-id]").value = "";
    input.classList.remove("is-invalid");

    if (query.length < 2) {
        results.classList.add("d-none");
        results.innerHTML = "";
        return;
    }

    const searchUrl = new URL("{{ route('blotters.residents.search') }}", window.location.origin);
    searchUrl.searchParams.set("q", query);

    let payload = null;
    if (axiosInstance) {
        const response = await axiosInstance.get(searchUrl.toString());
        payload = response.data;
    } else {
        const response = await fetch(searchUrl.toString(), {
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        payload = await response.json();
    }

    const rows = payload?.results || [];
    results.innerHTML = "";

    if (!rows.length) {
        const option = new Option("No residents found", "");
        option.disabled = true;
        results.appendChild(option);
        results.classList.remove("d-none");
        return;
    }

    rows.forEach((resident) => {
        const option = new Option(resident.text, resident.id);
        option.dataset.name = resident.name;
        option.dataset.text = resident.text;
        results.appendChild(option);
    });

    results.classList.remove("d-none");
}

window.openCreateModal = () => {
    blotterForm.reset();
    clearValidation();
    document.getElementById("blotterId").value = "";
    document.getElementById("caseNumberDisplay").value = "Auto-generated on save";
    resetParties();
    blotterModal.show();
};

async function editBlotter(id, url = null) {
    clearValidation();
    try {
        const response = await requestJson(url || `/blotters/${id}/edit`);
        const data = response?.data || {};
        resetParties();
        document.getElementById("blotterId").value = data.id || id;
        document.getElementById("caseNumberDisplay").value = data.case_number || "";
        blotterForm.querySelector("[name=\"incident_title\"]").value = data.incident_title || "";
        if (data.complainant_id) {
            setComplainantMode("resident", { id: data.complainant_id, text: data.complainant_name || "" }, data.complainant_name || "");
        } else {
            setComplainantMode("manual", null, data.complainant_name || "");
        }
        document.getElementById("respondentsContainer").innerHTML = "";
        (data.respondents?.length ? data.respondents : [{ name: data.respondent_name || "" }]).forEach((person) => addParty("respondent", person));
        document.getElementById("witnessesContainer").innerHTML = "";
        (data.witnesses || []).forEach((person) => addParty("witness", person));
        blotterForm.querySelector("[name=\"location\"]").value = data.location || "";
        blotterForm.querySelector("[name=\"incident_description\"]").value = data.incident_description || "";
        blotterForm.querySelector("[name=\"incident_date\"]").value = data.incident_date || "";
        blotterForm.querySelector("[name=\"status\"]").value = data.status || "pending";
        blotterModal.show();
    } catch (error) {
        showAlert("Failed to load blotter details.", "danger");
    }
}

async function deleteBlotter(id, url = null) {
    if (!confirm("Are you sure you want to delete this blotter record?")) return;
    try {
        const response = await requestJson(url || `/blotters/${id}`, { method: "delete" });
        showAlert(response?.message || "Blotter record deleted successfully.");
        updateDashboard(response?.dashboard);
        blottersTable?.ajax.reload(null, false);
    } catch (error) {
        showAlert(error.response?.data?.message || "Failed to delete blotter record.", "danger");
    }
}

blotterForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    clearValidation();

    if (!validateResidentSelections()) {
        return;
    }

    const id = document.getElementById("blotterId").value;
    const method = "post";
    const url = id ? `/blotters/${id}` : "/blotters";
    const data = new FormData(blotterForm);
    if (id) data.append("_method", "PUT");

    try {
        const response = await requestJson(url, { method, data });
        showAlert(response?.message || "Blotter record saved successfully.");
        updateDashboard(response?.dashboard);
        blotterModal.hide();
        blotterForm.reset();
        blottersTable?.ajax.reload(null, false);
    } catch (error) {
        if (error.response?.status === 422) {
            showFormErrors(error.response.data.errors || {});
            return;
        }

        const message = error.response?.data?.message
            || error.message
            || "An error occurred while saving.";
        showAlert(message, "danger");
    }
});

uploadZone.addEventListener("click", () => evidenceFile.click());
uploadZone.addEventListener("dragover", (event) => {
    event.preventDefault();
    uploadZone.classList.add("dragover");
});
uploadZone.addEventListener("dragleave", () => uploadZone.classList.remove("dragover"));
uploadZone.addEventListener("drop", (event) => {
    event.preventDefault();
    uploadZone.classList.remove("dragover");
    if (event.dataTransfer.files.length) {
        evidenceFile.files = event.dataTransfer.files;
        uploadLabel.textContent = event.dataTransfer.files[0].name;
    }
});
evidenceFile.addEventListener("change", () => {
    uploadLabel.textContent = evidenceFile.files[0]?.name || "Drop files here or click to browse";
});

evidenceForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const reference = evidenceForm.querySelector("[name=\"reference\"]").value.trim();
    const file = evidenceFile.files[0];
    if (!reference || !file) {
        showAlert("Select a blotter reference number and choose a file.", "danger");
        return;
    }

    const formData = new FormData();
    formData.append("evidence", file);
    formData.append("caption", file.name);
    const submitButton = evidenceForm.querySelector("button[type=\"submit\"]");
    submitButton.disabled = true;

    try {
        const response = await requestJson(`/blotters/${encodeURIComponent(reference)}/evidence`, {
            method: "post",
            data: formData,
        });
        showAlert(response?.message || "Supporting document attached successfully.");
        evidenceForm.reset();
        uploadLabel.textContent = "Drop files here or click to browse";
    } catch (error) {
        showAlert(error.response?.data?.message || "Failed to attach supporting document.", "danger");
    } finally {
        submitButton.disabled = false;
    }
});

document.getElementById("addRespondentBtn").addEventListener("click", () => addParty("respondent"));
document.getElementById("addWitnessBtn").addEventListener("click", () => addParty("witness"));

blotterForm.addEventListener("change", (event) => {
    if (!event.target.classList.contains("person-mode")) return;
    togglePersonMode(event.target.closest(".party-card"));
});

blotterForm.addEventListener("click", (event) => {
    const removeButton = event.target.closest(".remove-party");
    if (removeButton) {
        const section = removeButton.closest(".party-card");
        const type = section.dataset.personSection;
        section.remove();
        reindexParties(type);
        return;
    }

});

blotterForm.addEventListener("change", (event) => {
    if (!event.target.classList.contains("resident-results")) return;

    const option = event.target.selectedOptions[0];
    if (!option?.value) return;

    const section = event.target.closest(".party-card");
    const searchInput = section.querySelector(".resident-search");

    section.querySelector("[data-resident-id]").value = option.value;
    searchInput.value = option.dataset.text || option.textContent;
    searchInput.classList.remove("is-invalid");
    event.target.classList.add("d-none");
});

let residentSearchTimer = null;
blotterForm.addEventListener("input", (event) => {
    if (!event.target.classList.contains("resident-search")) return;
    clearTimeout(residentSearchTimer);
    residentSearchTimer = setTimeout(() => searchResidents(event.target).catch(() => {
        const section = event.target.closest(".party-card");
        const results = section.querySelector(".resident-results");
        results.innerHTML = "";
        const option = new Option("Unable to load residents", "");
        option.disabled = true;
        results.appendChild(option);
        results.classList.remove("d-none");
    }), 250);
});

document.addEventListener("DOMContentLoaded", () => {
    resetParties();

    if (window.Chart) {
        statusChart = new Chart(document.getElementById("statusChart"), {
            type: "doughnut",
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: ["#dc2626", "#f59e0b", "#16a34a", "#94a3b8"],
                    borderWidth: 0,
                }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: "62%",
                plugins: { legend: { position: "bottom", labels: { boxWidth: 12, usePointStyle: false } } },
            },
        });

        typeChart = new Chart(document.getElementById("typeChart"), {
            type: "bar",
            data: {
                labels: Object.keys(typeData),
                datasets: [{
                    data: Object.values(typeData),
                    backgroundColor: "#2563eb",
                    borderRadius: 6,
                }],
            },
            options: {
                indexAxis: "y",
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: "#edf2f7" } },
                    y: { grid: { display: false } },
                },
            },
        });
    }

    if (window.$ && $.fn.DataTable) {
        blottersTable = $("#blottersTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('blotters.data') }}",
                dataSrc: "data",
                headers: { "Accept": "application/json" },
            },
            columns: [
                { data: "case_number", name: "case_number" },
                { data: "complainant_display", name: "complainant_display", orderable: false },
                { data: "incident_type", name: "incident_title" },
                { data: "date_filed", name: "incident_date" },
                { data: "severity_badge", name: "severity", orderable: false, searchable: false },
                { data: "status_badge", name: "status", orderable: false },
                { data: "action", name: "action", orderable: false, searchable: false },
            ],
            order: [[3, "desc"]],
            pageLength: 7,
            lengthMenu: [[7, 10, 25, 50], [7, 10, 25, 50]],
            language: {
                search: "",
                searchPlaceholder: "Search...",
                info: "Showing _START_-_END_ of _TOTAL_ records",
                infoEmpty: "No blotter records available",
                lengthMenu: "Show _MENU_",
            },
        });
    }

    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-blotter-action]");
        if (!button) return;
        if (button.dataset.blotterAction === "edit") {
            editBlotter(button.dataset.blotterId, button.dataset.blotterEditUrl);
        }
        if (button.dataset.blotterAction === "delete") {
            deleteBlotter(button.dataset.blotterId, button.dataset.blotterDeleteUrl);
        }
    });
});
</script>
@endsection
