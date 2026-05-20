@extends("layouts.app")

@section("title", "Committee Records - Barangay Management System")
@section("page-title", "Committee Records & Reports")

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Committee Records & Reports</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Track committee records across all committees.</p>
    </div>
    @can('committee-records.manage')
        <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openCreateModal()"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Add Record
        </button>
    @endcan
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Committee</th>
                <th>Type</th>
                <th>Title</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->committee?->name }}</td>
                    <td><span class="badge" style="background:#eff6ff;color:#1a56db;">{{ $record->type_label }}</span></td>
                    <td>{{ $record->title }}</td>
                    <td>{{ ($record->record_date ?: $record->recorded_at)?->format("M d, Y") }}</td>
                    <td>{{ $record->status ?: '-' }}</td>
                    <td>
                        <a class="btn btn-sm btn-light" href="{{ route('committees.show', $record->committee_id) }}">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        @can('committee-records.manage')
                            <button class="btn btn-sm btn-light" onclick='editRecord(@json($record))'><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger" onclick="deleteRecord({{ $record->id }})"><i class="bi bi-trash"></i></button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">No records.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $records->links() }}
</div>

@can('committee-records.manage')
<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Report Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="recordForm">
                @csrf
                <input type="hidden" id="recordId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Committee</label>
                            <select class="form-select" name="committee_id" required>
                                <option value="">Select Committee</option>
                                @foreach($committees as $committee)
                                    <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Type</label>
                            <select class="form-select" name="record_type" required>
                                @foreach($recordTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-600">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Date</label>
                            <input type="date" class="form-control" name="recorded_at" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Category</label>
                            <input type="text" class="form-control" name="category">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <input type="text" class="form-control" name="status">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
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
@endcan
@endsection

@section("scripts")
@can('committee-records.manage')
<script>
const recordModal = new bootstrap.Modal(document.getElementById('recordModal'));
const recordForm = document.getElementById('recordForm');

function openCreateModal() {
    recordForm.reset();
    document.getElementById('recordId').value = '';
    recordModal.show();
}

function editRecord(record) {
    recordForm.reset();
    document.getElementById('recordId').value = record.id;
    recordForm.committee_id.value = record.committee_id;
    recordForm.record_type.value = record.record_type;
    recordForm.title.value = record.title;
    recordForm.recorded_at.value = record.recorded_at ? record.recorded_at.substring(0, 10) : '';
    recordForm.category.value = record.category || '';
    recordForm.status.value = record.status || '';
    recordForm.description.value = record.description || '';
    recordModal.show();
}

async function deleteRecord(id) {
    if (!confirm('Delete this record?')) return;

    const res = await fetch(`/reports/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    });

    if (res.ok) location.reload();
}

recordForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = document.getElementById('recordId').value;
    const payload = Object.fromEntries(new FormData(recordForm).entries());

    const res = await fetch(id ? `/reports/${id}` : '/reports', {
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
    alert(data.message || 'Unable to save record.');
});
</script>
@endcan
@endsection
