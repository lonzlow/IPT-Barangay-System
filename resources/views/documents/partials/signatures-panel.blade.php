@can('signatures.manage')
<div class="table-card mb-4" id="signaturesPanel">
    <div class="table-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="heading">Official Signatures</span>
        <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1" id="toggleSignatureForm"
            style="border-radius:7px;font-size:12px;font-weight:600;">
            <i class="bi bi-plus-lg"></i> Upload Signature
        </button>
    </div>
    <div class="px-3 py-3 border-bottom bg-white d-none" id="signatureUploadForm">
        <form id="signatureForm" enctype="multipart/form-data">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Official</label>
                    <select name="official_id" id="signatureOfficialSelect" class="form-select" style="font-size:13px;border-radius:8px;" required>
                        <option value="">-- Select Official --</option>
                        @foreach($officials as $official)
                            @php $r = $official->resident; @endphp
                            <option value="{{ $official->id }}">
                                {{ $r ? trim($r->first_name.' '.$r->last_name) : 'Official' }}
                                ({{ $official->role?->role_name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Label (optional)</label>
                    <input type="text" name="label" class="form-control" style="font-size:13px;border-radius:8px;" placeholder="e.g. Captain">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Image (PNG/JPG, max 2MB)</label>
                    <input type="file" name="signature" accept="image/*" class="form-control" style="font-size:13px;border-radius:8px;" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" id="signatureUploadBtn" style="border-radius:8px;font-size:13px;font-weight:600;">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="p-3">
        <div class="row g-3" id="signaturesGrid">
            @forelse($signatures as $sig)
                @php
                    $off = $sig->official?->resident;
                    $name = $off ? trim($off->first_name.' '.$off->last_name) : 'Official';
                    $imgUrl = $sig->publicUrl();
                @endphp
                <div class="col-6 col-md-4 col-lg-3" data-signature-id="{{ $sig->id }}">
                    <div class="border rounded p-2 h-100" style="background:#fff;">
                        <div class="text-center mb-2" style="height:72px;display:flex;align-items:center;justify-content:center;">
                            <img src="{{ $imgUrl }}" alt="{{ $name }}" style="max-height:68px;max-width:100%;object-fit:contain;">
                        </div>
                        <div style="font-size:12px;font-weight:700;color:#0f172a;">{{ $name }}</div>
                        @if($sig->label)
                            <div style="font-size:11px;color:#64748b;">{{ $sig->label }}</div>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 mt-2 delete-signature-btn"
                            data-signature-id="{{ $sig->id }}" style="border-radius:6px;font-size:11px;">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted" style="font-size:13px;" id="signaturesEmpty">No signatures uploaded yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endcan
