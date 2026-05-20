@extends("layouts.app")

@section("title", $committee->name . " - Committee Workspace")
@section("page-title", $committee->name)

@section("styles")
<style>
    .workspace-hero { background:#fff; border:1px solid var(--border); border-radius:12px; padding:20px; }
    .workspace-title { font-size:20px; font-weight:800; color:#0f172a; line-height:1.25; margin:0; }
    .workspace-desc { font-size:13px; color:#64748b; line-height:1.5; margin:8px 0 0; max-width:860px; }
    .workspace-tabs .nav-link { border:1px solid var(--border); color:#475569; font-size:12.5px; font-weight:700; border-radius:8px; padding:8px 11px; background:#fff; }
    .workspace-tabs .nav-link.active { background:#1a56db; border-color:#1a56db; color:#fff; }
    .record-card { border:1px solid #e2e8f0; border-radius:10px; padding:14px; background:#fff; height:100%; }
    .record-title { font-size:13.5px; font-weight:800; color:#0f172a; margin:0; line-height:1.3; }
    .record-meta { font-size:11.5px; color:#64748b; display:flex; gap:8px; flex-wrap:wrap; margin-top:7px; }
    .record-desc { font-size:12.5px; color:#475569; margin:10px 0 0; line-height:1.45; }
    .record-preview { width:100%; aspect-ratio:16 / 10; border-radius:8px; overflow:hidden; background:#f1f5f9; border:1px solid #e2e8f0; margin-bottom:12px; }
    .record-preview img, .record-preview video { width:100%; height:100%; object-fit:cover; display:block; }
    .record-preview video { background:#0f172a; }
    .empty-panel { border:1px dashed #cbd5e1; border-radius:10px; padding:22px; text-align:center; color:#64748b; font-size:13px; background:#fff; }
    .summary-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:10px; }
    .summary-box { background:#f8fafc; border:1px solid #edf2f7; border-radius:10px; padding:12px; }
    .summary-value { font-family:"DM Mono", monospace; font-size:22px; font-weight:800; color:#0f172a; line-height:1; }
    .summary-label { font-size:11.5px; color:#64748b; margin-top:5px; }
</style>
@endsection

@section("content")
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <a href="{{ route('committees.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;font-size:13px;font-weight:700;">
        <i class="bi bi-arrow-left me-1"></i> Committees
    </a>
    @can('committee-records.manage')
        <button class="btn btn-primary d-flex align-items-center gap-2" onclick="openRecordModal()"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Add Record
        </button>
    @endcan
</div>

<div class="workspace-hero mb-4">
    <div class="d-flex justify-content-between gap-3 flex-wrap">
        <div>
            <h5 class="workspace-title">{{ $committee->name }}</h5>
            <p class="workspace-desc">{{ $committee->description ?: 'No description has been added yet.' }}</p>
        </div>
        <div class="text-end">
            <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">Chairperson</div>
            <div style="font-size:14px;font-weight:800;color:#0f172a;">
                {{ $committee->headOfficial?->resident?->first_name ? trim($committee->headOfficial->resident->first_name . ' ' . $committee->headOfficial->resident->last_name) : 'Unassigned' }}
            </div>
            <div style="font-size:12px;color:#64748b;">{{ $committee->chair_label ?: 'Committee Chair' }}</div>
        </div>
    </div>

    <div class="summary-grid mt-4">
        <div class="summary-box">
            <div class="summary-value">{{ $committee->records->count() }}</div>
            <div class="summary-label">Total records</div>
        </div>
        <div class="summary-box">
            <div class="summary-value">{{ $recordGroups['media']->count() }}</div>
            <div class="summary-label">Photos & videos</div>
        </div>
        <div class="summary-box">
            <div class="summary-value">{{ $recordGroups['activities']->count() }}</div>
            <div class="summary-label">Activities & trainings</div>
        </div>
        <div class="summary-box">
            <div class="summary-value">{{ $recordGroups['reports']->count() }}</div>
            <div class="summary-label">Reports & policies</div>
        </div>
    </div>
</div>

<ul class="nav workspace-tabs gap-2 mb-3" id="committeeTabs" role="tablist">
    @foreach([
        'overview' => 'Overview',
        'media' => 'Photos / Videos',
        'activities' => 'Activities',
        'accomplishments' => 'Accomplishments',
        'reports' => 'Reports / Records',
        'attendance' => 'Attendance',
        'inventory' => 'Inventory / Assets',
        'partnerships' => 'Partners / Certificates',
    ] as $key => $label)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $key }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $key }}" type="button" role="tab">
                {{ $label }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <div class="table-card">
            <div class="table-header">
                <div class="heading">Allowed Record Types</div>
            </div>
            <div class="p-3 d-flex flex-wrap gap-2">
                @foreach($recordTypes as $label)
                    <span class="badge rounded-pill" style="background:#eff6ff;color:#1a56db;padding:7px 10px;">{{ $label }}</span>
                @endforeach
            </div>
        </div>
    </div>

    @foreach([
        'media' => 'Photos and videos',
        'activities' => 'Activities and trainings',
        'accomplishments' => 'Accomplishments',
        'reports' => 'Reports, policies, logs, and formal records',
        'attendance' => 'Attendance sheets',
        'inventory' => 'Inventory, personnel lists, and profiles',
        'partnerships' => 'Partnership records, certificates, and recognition',
    ] as $key => $heading)
        <div class="tab-pane fade" id="{{ $key }}" role="tabpanel">
            <div class="table-card">
                <div class="table-header">
                    <div class="heading">{{ $heading }}</div>
                    <span class="badge bg-light text-dark">{{ $recordGroups[$key]->count() }} records</span>
                </div>
                <div class="p-3">
                    @if($recordGroups[$key]->isEmpty())
                        <div class="empty-panel">No records in this section yet.</div>
                    @else
                        <div class="row g-3">
                            @foreach($recordGroups[$key] as $record)
                                <div class="col-md-6 col-xl-4">
                                    <div class="record-card">
                                        @if($record->file_path && $record->record_type === 'photo')
                                            <div class="record-preview">
                                                <img src="{{ $record->file_path }}" alt="{{ $record->title }}">
                                            </div>
                                        @elseif($record->file_path && $record->record_type === 'video')
                                            <div class="record-preview">
                                                <video src="{{ $record->file_path }}" controls preload="metadata"></video>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between gap-2">
                                            <div>
                                                <p class="record-title">{{ $record->title }}</p>
                                                <div class="record-meta">
                                                    <span>{{ $record->type_label }}</span>
                                                    @if($record->category)<span>{{ $record->category }}</span>@endif
                                                    <span>{{ ($record->record_date ?: $record->recorded_at)?->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                            @can('committee-records.manage')
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-light" title="Edit" onclick='editRecord(@json($record))'><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-light text-danger" title="Delete" onclick="deleteRecord({{ $record->id }})"><i class="bi bi-trash"></i></button>
                                                </div>
                                            @endcan
                                        </div>
                                        @if($record->description)
                                            <p class="record-desc">{{ $record->description }}</p>
                                        @endif
                                        <div class="record-meta">
                                            @if($record->quantity !== null)<span>Qty: {{ $record->quantity }}</span>@endif
                                            @if($record->amount !== null)<span>Amount: PHP {{ number_format((float) $record->amount, 2) }}</span>@endif
                                            @if($record->partner_name)<span>Partner: {{ $record->partner_name }}</span>@endif
                                            @if($record->status)<span>Status: {{ $record->status }}</span>@endif
                                        </div>
                                        @if($record->file_path)
                                            <a href="{{ $record->file_path }}" class="btn btn-sm btn-outline-primary mt-3" target="_blank" rel="noopener">
                                                <i class="bi bi-link-45deg me-1"></i> Open File
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@can('committee-records.manage')
<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Committee Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="recordForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="recordId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Record Type</label>
                            <select class="form-select" name="record_type" required>
                                @foreach($recordTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Category</label>
                            <input type="text" class="form-control" name="category" placeholder="Patrols, medical mission, TODA, etc.">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-600">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Date</label>
                            <input type="date" class="form-control" name="record_date">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-600">Quantity</label>
                            <input type="number" min="0" class="form-control" name="quantity">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-600">Amount</label>
                            <input type="number" min="0" step="0.01" class="form-control" name="amount">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-600">Status</label>
                            <input type="text" class="form-control" name="status">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-600">Partner</label>
                            <input type="text" class="form-control" name="partner_name">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Upload Photo / Video</label>
                            <input type="file" class="form-control" name="media_file" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska">
                            <div class="form-text">Photos: JPG, PNG, WEBP, GIF up to 10 MB. Videos: MP4, MOV, AVI, WEBM, MKV up to 200 MB.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">External File URL / Existing Path</label>
                            <input type="text" class="form-control" name="file_path" placeholder="/storage/committee-media/... or https://...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
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

function openRecordModal() {
    recordForm.reset();
    document.getElementById('recordId').value = '';
    recordModal.show();
}

function editRecord(record) {
    recordForm.reset();
    document.getElementById('recordId').value = record.id;
    recordForm.record_type.value = record.record_type || 'report';
    recordForm.category.value = record.category || '';
    recordForm.title.value = record.title || '';
    recordForm.record_date.value = record.record_date ? record.record_date.substring(0, 10) : (record.recorded_at ? record.recorded_at.substring(0, 10) : '');
    recordForm.description.value = record.description || '';
    recordForm.quantity.value = record.quantity ?? '';
    recordForm.amount.value = record.amount ?? '';
    recordForm.status.value = record.status || '';
    recordForm.partner_name.value = record.partner_name || '';
    recordForm.file_path.value = record.file_path || '';
    recordModal.show();
}

async function deleteRecord(id) {
    if (!confirm('Delete this committee record?')) return;

    const res = await fetch(`/committee-records/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    });

    if (res.ok) location.reload();
}

recordForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = document.getElementById('recordId').value;
    const payload = new FormData(recordForm);

    for (const [key, value] of [...payload.entries()]) {
        if (value === '') payload.delete(key);
        if (key === 'media_file' && value instanceof File && value.size === 0) payload.delete(key);
    }

    if (id) {
        payload.append('_method', 'PUT');
    }

    ['quantity', 'amount'].forEach((key) => {
        if (payload.get(key) === null) payload.delete(key);
    });

    const res = await fetch(id ? `/committee-records/${id}` : `{{ route('committees.records.store', $committee) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: payload
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
