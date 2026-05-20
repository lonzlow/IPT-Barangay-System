@extends("layouts.app")

@section("title", "Committees - Barangay Management System")
@section("page-title", "Committee Management")

@section("styles")
<style>
    .committee-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
    .committee-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:18px; min-height:236px; display:flex; flex-direction:column; gap:14px; transition:box-shadow .18s, transform .18s; }
    .committee-card:hover { box-shadow:0 8px 24px rgba(15,23,42,.08); transform:translateY(-1px); }
    .committee-icon { width:42px; height:42px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:#eff6ff; color:#1a56db; font-size:20px; }
    .committee-title { font-size:15px; font-weight:800; color:#0f172a; line-height:1.25; margin:0; }
    .committee-desc { font-size:12.5px; color:#64748b; line-height:1.45; margin:0; }
    .metric-row { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:8px; }
    .metric-box { background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; padding:9px; }
    .metric-value { font-family:"DM Mono", monospace; font-size:17px; font-weight:700; color:#0f172a; line-height:1; }
    .metric-label { font-size:10.5px; color:#64748b; margin-top:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .chair-line { font-size:12.5px; color:#475569; }
    .chair-line strong { color:#0f172a; }
</style>
@endsection

@section("content")
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Committee Dashboard</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Open a committee workspace to manage media, activities, reports, attendance, inventory, partners, and committee-specific records.</p>
    </div>
    @can('committees.manage')
        <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCommitteeModal()"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Add Committee
        </button>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="committee-grid">
    @forelse($committees as $committee)
        <div class="committee-card">
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div class="committee-icon"><i class="bi bi-diagram-3-fill"></i></div>
                @can('committees.manage')
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-light" title="Edit" onclick='editCommittee(@json($committee))'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-light text-danger" title="Delete" onclick="deleteCommittee({{ $committee->id }})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                @endcan
            </div>

            <div>
                <h6 class="committee-title">{{ $committee->name }}</h6>
                <div class="chair-line mt-2">
                    <i class="bi bi-person-badge me-1"></i>
                    <strong>{{ $committee->chair_label ?: 'Chairperson' }}:</strong>
                    {{ $committee->headOfficial?->resident?->first_name ? trim($committee->headOfficial->resident->first_name . ' ' . $committee->headOfficial->resident->last_name) : 'Unassigned' }}
                </div>
            </div>

            <p class="committee-desc">{{ $committee->description ?: 'No description has been added yet.' }}</p>

            <div class="metric-row mt-auto">
                <div class="metric-box">
                    <div class="metric-value">{{ $committee->records_count }}</div>
                    <div class="metric-label">Records</div>
                </div>
                <div class="metric-box">
                    <div class="metric-value">{{ $committee->media_count }}</div>
                    <div class="metric-label">Media</div>
                </div>
                <div class="metric-box">
                    <div class="metric-value">{{ $committee->activity_count }}</div>
                    <div class="metric-label">Activities</div>
                </div>
            </div>

            <a href="{{ route('committees.show', $committee) }}" class="btn btn-outline-primary w-100 mt-1" style="border-radius:8px;font-size:13px;font-weight:700;">
                Open Workspace
            </a>
        </div>
    @empty
        <div class="table-card p-4 text-center">
            <div class="fw-bold mb-1">No committees found</div>
            <div class="text-muted" style="font-size:13px;">Seed the default committees or add one manually.</div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $committees->links() }}
</div>

@can('committees.manage')
<div class="modal fade" id="committeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Committee Setup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="committeeForm">
                @csrf
                <input type="hidden" id="committeeId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-600">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Slug</label>
                            <input type="text" class="form-control" name="slug">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Chair Label</label>
                            <input type="text" class="form-control" name="chair_label" placeholder="Kgd Name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Chairperson</label>
                            <select class="form-select" name="chairperson_id">
                                <option value="">Select role ID 5 official</option>
                                @foreach($chairmanCandidates as $official)
                                    <option value="{{ $official->id }}">
                                        {{ trim(($official->resident?->first_name ?? '') . ' ' . ($official->resident?->last_name ?? '')) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Allowed Record Types</label>
                            <select class="form-select" name="allowed_record_types[]" multiple size="8">
                                @foreach($recordTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl to select multiple types.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Committee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@section("scripts")
@can('committees.manage')
<script>
const committeeModal = new bootstrap.Modal(document.getElementById('committeeModal'));
const committeeForm = document.getElementById('committeeForm');

function openCommitteeModal() {
    committeeForm.reset();
    document.getElementById('committeeId').value = '';
    committeeModal.show();
}

function editCommittee(committee) {
    committeeForm.reset();
    document.getElementById('committeeId').value = committee.id;
    committeeForm.name.value = committee.name || '';
    committeeForm.slug.value = committee.slug || '';
    committeeForm.chair_label.value = committee.chair_label || '';
    committeeForm.chairperson_id.value = committee.chairperson_id || '';
    committeeForm.description.value = committee.description || '';

    const selectedTypes = committee.allowed_record_types || [];
    [...committeeForm.querySelector('[name="allowed_record_types[]"]').options].forEach((option) => {
        option.selected = selectedTypes.includes(option.value);
    });

    committeeModal.show();
}

async function deleteCommittee(id) {
    if (!confirm('Delete this committee?')) return;

    const res = await fetch(`/committees/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    });

    if (res.ok) location.reload();
}

committeeForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = document.getElementById('committeeId').value;
    const formData = new FormData(committeeForm);
    const payload = Object.fromEntries(formData.entries());
    payload.allowed_record_types = formData.getAll('allowed_record_types[]');

    const res = await fetch(id ? `/committees/${id}` : '/committees', {
        method: id ? 'PUT' : 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    if (res.ok) {
        location.reload();
        return;
    }

    const data = await res.json().catch(() => ({}));
    alert(data.message || 'Unable to save committee.');
});
</script>
@endcan
@endsection
