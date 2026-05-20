@extends("layouts.app")

@section("title", "Blotter Records - Barangay Management System")
@section("page-title", "Blotter Management")

@section("content")

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">
            Blotter Records
        </h5>

        <p class="mb-0" style="font-size:13px;color:#64748b;">
            Manage barangay blotter complaints, respondents, and witnesses.
        </p>
    </div>

    <button
        class="btn btn-primary d-flex align-items-center gap-2"
        onclick="openCreateModal()"
        style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;"
    >
        <i class="bi bi-plus-lg"></i>
        Add Blotter
    </button>
</div>

<div class="table-card">

    <table class="table table-hover">

        <thead>
            <tr>
                <th>Case No.</th>
                <th>Complainant</th>
                <th>Respondents</th>
                <th>Witnesses</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

            @forelse($blotters as $blotter)

                <tr>

                    <td class="fw-600">
                        {{ $blotter->case_number }}
                    </td>

                    <td>
                        @if($blotter->complainant_resident)
                            {{ $blotter->complainant_resident?->first_name . ' ' . $blotter->complainant_resident?->last_name }}
                        @else
                            {{ $blotter->complainant_name }}
                        @endif
                    </td>

                    <td>

                        @forelse($blotter->respondents as $respondent)

                            @if($respondent->respondent)

                                {{ $respondent->respondent->first_name }}
                                {{ $respondent->respondent->middle_name }}
                                {{ $respondent->respondent->last_name }}
                                {{ $respondent->respondent->suffix }}

                            @else

                                {{ $respondent->respondent_name }}

                            @endif

                            @if(!$loop->last)
                                <br>
                            @endif

                        @empty

                            <span class="text-muted">
                                —
                            </span>

                        @endforelse

                    </td>

                    <td>

                        @forelse($blotter->witnesses as $witness)

                            @if($witness->resident_witness)

                                {{ $witness->resident_witness->first_name }}
                                {{ $witness->resident_witness->middle_name }}
                                {{ $witness->resident_witness->last_name }}
                                {{ $witness->resident_witness->suffix }}

                            @endif

                            @if(!$loop->last)
                                <br>
                            @endif

                        @empty

                            <span class="text-muted">
                                —
                            </span>

                        @endforelse

                    </td>

                    <td>

                        @php
                            $statusClass = match($blotter->status) {
                                'pending' => 'bg-danger-subtle text-danger',
                                'under investigation' => 'bg-warning-subtle text-warning',
                                'resolved' => 'bg-success-subtle text-success',
                                'referred' => 'bg-info-subtle text-info',
                                default => 'bg-secondary-subtle text-secondary'
                            };
                        @endphp

                        <span class="badge {{ $statusClass }}">
                            {{ ucfirst($blotter->status) }}
                        </span>

                    </td>

                    <td>
                        {{ optional($blotter->incident_date)->format('M d, Y') }}
                    </td>

                    <td>

                        <a
                            href="{{ route('blotters.show', ['blotter' => $blotter->id]) }}"
                            class="btn btn-sm btn-light"
                        >
                            <i class="bi bi-eye"></i>
                        </a>

                        <button
                            class="btn btn-sm btn-light"
                            onclick="editBlotter('{{ $blotter->id }}')"
                        >
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button
                            class="btn btn-sm btn-light"
                            onclick="deleteBlotter('{{ $blotter->id }}')"
                        >
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" class="text-center py-4">

                        No blotter records found.

                        <a href="javascript:openCreateModal()">
                            Add one.
                        </a>

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{-- MODAL --}}
<div class="modal fade" id="blotterModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title fw-800">
                    Blotter Details
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <form id="blotterForm">

                @csrf

                <input
                    type="hidden"
                    id="blotterId"
                    name="id"
                >

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label fw-600">
                            Case Number
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="case_number"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-600">
                            Complainant Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="complainant_name"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-600">
                            Location
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="location"
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-600">
                            Incident Description
                        </label>

                        <textarea
                            class="form-control"
                            name="incident_description"
                            rows="3"
                            required
                        ></textarea>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-600">
                                Incident Date
                            </label>

                            <input
                                type="datetime-local"
                                class="form-control"
                                name="incident_date"
                                required
                            >

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-600">
                                Status
                            </label>

                            <select
                                class="form-select"
                                name="status"
                            >
                                <option value="pending">Pending</option>
                                <option value="under investigation">Under Investigation</option>
                                <option value="resolved">Resolved</option>
                                <option value="referred">Referred</option>
                            </select>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Blotter
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push("scripts")

<script>

const blotterModal = new bootstrap.Modal(
    document.getElementById("blotterModal")
);

window.openCreateModal = () => {

    document.getElementById("blotterForm").reset();

    document.getElementById("blotterId").value = "";

    blotterModal.show();
};

async function editBlotter(id) {

    try {

        const res = await fetch(`/blotters/${id}/edit`, {
            headers: {
                "Accept": "application/json"
            }
        });

        const data = await res.json();

        if (data.success) {

            const b = data.data;

            document.getElementById("blotterId").value
                = b.id;

            document.querySelector("[name='case_number']").value
                = b.case_number || "";

            document.querySelector("[name='complainant_name']").value
                = b.complainant_name || "";

            document.querySelector("[name='location']").value
                = b.location || "";

            document.querySelector("[name='incident_description']").value
                = b.incident_description || "";

            if (b.incident_date) {

                const date = new Date(b.incident_date);

                document.querySelector("[name='incident_date']").value
                    = date.toISOString().slice(0, 16);
            }

            document.querySelector("[name='status']").value
                = b.status || "pending";

            blotterModal.show();
        }

    } catch (err) {

        console.error(err);

        alert("Failed to load blotter.");
    }
}

async function deleteBlotter(id) {

    if (!confirm(
        "Are you sure you want to delete this blotter record?"
    )) return;

    try {

        const res = await fetch(`/blotters/${id}`, {

            method: "DELETE",

            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        });

        if (res.ok) {

            alert("Blotter deleted successfully");

            location.reload();

        } else {

            alert("Failed to delete blotter.");
        }

    } catch (err) {

        console.error(err);

        alert("An error occurred.");
    }
}

document.getElementById("blotterForm")
.addEventListener("submit", async (e) => {

    e.preventDefault();

    const id = document.getElementById("blotterId").value;

    const url = id
        ? `/blotters/${id}`
        : "/blotters";

    const method = id
        ? "PUT"
        : "POST";

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

            alert("Blotter saved successfully");

            blotterModal.hide();

            location.reload();

        } else {

            const errorData = await res.json();

            console.error(errorData);

            alert(
                "Error: "
                + JSON.stringify(
                    errorData.errors
                    || errorData.message
                )
            );
        }

    } catch (err) {

        console.error(err);

        alert("An error occurred.");
    }
});

</script>

@endpush
