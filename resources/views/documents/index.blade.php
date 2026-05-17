@extends('layouts.app')

@section('title', 'Barangay Documents')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Barangay Documents</h1>
                <a href="{{ route('documents.create') }}" class="btn btn-primary" style="border-radius:8px;padding:10px 20px;">
                    <i class="bi bi-plus-circle"></i> Issue New Document
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small style="color:#94a3b8;font-weight:600;">Total Documents</small>
                            <p class="fw-bold" style="font-size:24px;color:#1e293b;margin:0;">{{ $documents->total() }}</p>
                        </div>
                        <i class="bi bi-file-text" style="font-size:32px;color:#1a56db;opacity:0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small style="color:#94a3b8;font-weight:600;">Active Documents</small>
                            <p class="fw-bold" style="font-size:24px;color:#1e293b;margin:0;">
                                {{ count(array_filter($documents->items(), fn($d) => $d->status === 'Issued')) }}
                            </p>
                        </div>
                        <i class="bi bi-check-circle" style="font-size:32px;color:#10b981;opacity:0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small style="color:#94a3b8;font-weight:600;">Document Types</small>
                            <p class="fw-bold" style="font-size:24px;color:#1e293b;margin:0;">{{ $templates->count() }}</p>
                        </div>
                        <i class="bi bi-file-earmark-pdf" style="font-size:32px;color:#f59e0b;opacity:0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-header" style="background:#f9fafb;border-bottom:1px solid #e2e8f0;border-radius:12px 12px 0 0;padding:16px;">
                    <h6 class="fw-600 mb-0" style="color:#1e293b;">Recent Documents</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:14px;">
                            <thead style="background:#f9fafb;border-bottom:1px solid #e2e8f0;">
                                <tr>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Reference</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Resident</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Document Type</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Issued Date</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Valid Until</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Status</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                    <tr>
                                        <td style="padding:12px 16px;">
                                            <strong>{{ $document->reference_number }}</strong>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            {{ $document->resident->first_name }} {{ $document->resident->last_name }}
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span class="badge bg-light text-secondary" style="font-size:11px;">
                                                {{ $document->template->name }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            {{ $document->issued_date->format('M d, Y') }}
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @if($document->valid_until)
                                                <small>{{ $document->valid_until->format('M d, Y') }}</small>
                                            @else
                                                <small style="color:#94a3b8;">No expiration</small>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @if($document->status === 'Issued')
                                                <span class="badge bg-success">Issued</span>
                                            @elseif($document->status === 'Revoked')
                                                <span class="badge bg-danger">Revoked</span>
                                            @else
                                                <span class="badge bg-warning">Expired</span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('documents.show', $document->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:6px;padding:4px 8px;">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ route('documents.downloadPdf', $document->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:6px;padding:4px 8px;">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4" style="color:#94a3b8;">
                                            <i class="bi bi-inbox" style="font-size:32px;opacity:0.3;display:block;margin-bottom:8px;"></i>
                                            No documents issued yet. <a href="{{ route('documents.create') }}">Issue your first document</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer" style="background:#f9fafb;border-top:1px solid #e2e8f0;">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('styles')
<style>
    .doc-shortcut-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }
    .doc-shortcut {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 16px 18px;
        display: flex; flex-direction: column;
        align-items: center; gap: 10px;
        cursor: pointer; transition: all .2s;
        text-align: center; text-decoration: none; color: inherit;
    }
    .doc-shortcut:hover {
        border-color: #1a56db;
        box-shadow: 0 4px 18px rgba(26,86,219,.1);
        transform: translateY(-2px); color: inherit;
    }
    .doc-shortcut .ds-icon {
        width: 52px; height: 52px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
    }
    .doc-shortcut .ds-label { font-size: 12.5px; font-weight: 700; color: #0f172a; line-height: 1.35; }
    .doc-shortcut .ds-count { font-size: 11px; color: #94a3b8; font-weight: 500; }

    .dsi-blue   { background: #eff6ff; color: #1a56db; }
    .dsi-violet { background: #f5f3ff; color: #7c3aed; }
    .dsi-green  { background: #f0fdf4; color: #16a34a; }
    .dsi-teal   { background: #f0fdfa; color: #0d9488; }
    .dsi-orange { background: #fff7ed; color: #ea580c; }

    .doc-stat {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 16px 20px; display: flex; align-items: center; gap: 14px;
    }
    .doc-stat .ds-num {
        font-size: 26px; font-weight: 800;
        font-family: 'DM Mono', monospace; color: #0f172a; line-height: 1;
    }
    .doc-stat .ds-lbl { font-size: 12px; color: #64748b; font-weight: 500; margin-top: 3px; }
    .doc-stat .ds-ico {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }

    .badge-issued    { background: #dcfce7; color: #15803d; }
    .badge-released  { background: #eff6ff; color: #1d4ed8; }
    .badge-cancelled { background: #fee2e2; color: #dc2626; }

    .preview-panel {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;
    }
    .preview-panel .preview-header {
        background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        padding: 14px 20px; display: flex; align-items: center; gap: 10px;
    }
    .preview-panel .preview-header .ph-title { font-size: 13.5px; font-weight: 700; color: #0f172a; flex: 1; }
    .preview-body { padding: 24px; }

    .doc-paper {
        background: #fff; border: 1px solid #d1d5db; border-radius: 4px;
        padding: 32px 36px; font-size: 12px; line-height: 1.8; color: #374151;
        box-shadow: 0 2px 8px rgba(0,0,0,.06); position: relative;
    }
    .doc-paper .dp-letterhead {
        text-align: center; border-bottom: 2px solid #1a56db;
        padding-bottom: 14px; margin-bottom: 18px;
    }
    .doc-paper .dp-letterhead .dp-brgy { font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: .3px; }
    .doc-paper .dp-letterhead .dp-gov  { font-size: 10.5px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
    .doc-paper .dp-title {
        text-align: center; font-size: 13px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 1.5px;
        margin: 16px 0 18px; text-decoration: underline;
    }
    .doc-paper .dp-body { text-align: justify; font-size: 12px; }
    .doc-paper .dp-sig  { margin-top: 36px; display: flex; justify-content: space-between; }
    .doc-paper .dp-sig-block { text-align: center; font-size: 11.5px; }
    .doc-paper .dp-sig-block .sig-name {
        font-weight: 700; border-top: 1px solid #374151;
        padding-top: 4px; margin-top: 32px;
    }
    .doc-paper .watermark {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%,-50%) rotate(-30deg);
        font-size: 48px; font-weight: 900;
        color: rgba(26,86,219,.05);
        pointer-events: none; letter-spacing: 4px;
        text-transform: uppercase; white-space: nowrap;
    }

    .form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; }
    .form-card .fc-heading { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .form-card .fc-sub     { font-size: 12px; color: #64748b; margin-bottom: 18px; }
</style>
@endsection

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Document Issuance</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Issue, track, and manage barangay certificates and clearances.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13px;font-weight:600;padding:8px 16px;">
            <i class="bi bi-clock-history"></i> Request Queue
            <span class="badge bg-warning text-dark ms-1">5</span>
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> New Request
        </button>
    </div>
</div>

{{-- ── SHORTCUT BUTTONS ── --}}
<div class="section-heading">Issue Document</div>
<div class="doc-shortcut-grid mb-4">
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-blue"><i class="bi bi-heart-pulse-fill"></i></div>
        <div class="ds-label">Indigency<br>Certificate</div>
        <div class="ds-count">148 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-violet"><i class="bi bi-house-fill"></i></div>
        <div class="ds-label">Residency<br>Certificate</div>
        <div class="ds-count">95 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-green"><i class="bi bi-patch-check-fill"></i></div>
        <div class="ds-label">Barangay<br>Clearance</div>
        <div class="ds-count">212 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-teal"><i class="bi bi-award-fill"></i></div>
        <div class="ds-label">Good Moral<br>Certificate</div>
        <div class="ds-count">63 issued this month</div>
    </a>
    <a href="#" class="doc-shortcut">
        <div class="ds-icon dsi-orange"><i class="bi bi-shop-window"></i></div>
        <div class="ds-label">Business<br>Clearance</div>
        <div class="ds-count">31 issued this month</div>
    </a>
</div>

{{-- ── STAT MINI CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#eff6ff;color:#1a56db;"><i class="bi bi-file-earmark-check-fill"></i></div>
            <div><div class="ds-num">549</div><div class="ds-lbl">Issued this month</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="ds-num">5</div><div class="ds-lbl">Pending requests</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-printer-fill"></i></div>
            <div><div class="ds-num">12</div><div class="ds-lbl">Ready to print</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="doc-stat">
            <div class="ds-ico" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-circle-fill"></i></div>
            <div><div class="ds-num">3</div><div class="ds-lbl">Cancelled today</div></div>
        </div>
    </div>
</div>

{{-- ── RECENT DOCS + PENDING ── --}}
<div class="row g-3 mb-4">

    {{-- Recent Documents Issued --}}
    <div class="col-lg-7">
        <div class="table-card h-100">
            <div class="table-header">
                <span class="heading">Recent Documents Issued</span>
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size:12.5px;border-radius:7px;">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Control No.</th>
                            <th>Resident</th>
                            <th>Document Type</th>
                            <th>Date Issued</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $recent = [
                            ['BRG-2025-0841','Maria Santos',   'Barangay Clearance',     'Feb 24, 2025','released'],
                            ['BRG-2025-0840','Carlo Mendoza',  'Indigency Certificate',  'Feb 24, 2025','issued'],
                            ['BRG-2025-0839','Ana Reyes',      'Good Moral Certificate', 'Feb 23, 2025','issued'],
                            ['BRG-2025-0838','Luz Villanueva', 'Residency Certificate',  'Feb 23, 2025','released'],
                            ['BRG-2025-0837','Ernesto Lim',    'Business Clearance',     'Feb 22, 2025','issued'],
                            ['BRG-2025-0836','Rosa Aquino',    'Barangay Clearance',     'Feb 22, 2025','cancelled'],
                            ['BRG-2025-0835','Juan dela Cruz', 'Indigency Certificate',  'Feb 21, 2025','released'],
                        ];
                        $badgeMap = ['issued'=>['badge-issued','Issued'],'released'=>['badge-released','Released'],'cancelled'=>['badge-cancelled','Cancelled']];
                        @endphp
                        @foreach($recent as $doc)
                        @php [$ctrl,$name,$type,$date,$status] = $doc; [$bc,$bl] = $badgeMap[$status]; @endphp
                        <tr>
                            <td style="font-family:'DM Mono',monospace;font-size:12px;color:#64748b;">{{ $ctrl }}</td>
                            <td style="font-weight:600;">{{ $name }}</td>
                            <td style="font-size:13px;">{{ $type }}</td>
                            <td style="font-size:12.5px;color:#64748b;">{{ $date }}</td>
                            <td><span class="status-badge {{ $bc }}">{{ $bl }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-light" title="Print" style="border-radius:6px;padding:3px 8px;">
                                    <i class="bi bi-printer" style="font-size:13px;"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex align-items-center justify-content-between px-4 py-3"
                 style="border-top:1px solid #f1f5f9;font-size:13px;color:#64748b;">
                <span>Showing <strong>1–7</strong> of <strong>549</strong> documents</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0" style="gap:3px;">
                        <li class="page-item disabled"><a class="page-link" style="border-radius:6px;">&laquo;</a></li>
                        <li class="page-item active"><a class="page-link" style="border-radius:6px;">1</a></li>
                        <li class="page-item"><a class="page-link" style="border-radius:6px;">2</a></li>
                        <li class="page-item"><a class="page-link" style="border-radius:6px;">&raquo;</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="col-lg-5">
        <div class="table-card h-100">
            <div class="table-header">
                <span class="heading">
                    Pending Requests
                    <span class="badge ms-1" style="background:#fef3c7;color:#b45309;font-size:11px;border-radius:20px;padding:2px 9px;">5</span>
                </span>
                <button class="btn btn-sm btn-warning d-flex align-items-center gap-1"
                        style="font-size:12.5px;border-radius:7px;font-weight:600;">
                    <i class="bi bi-check2-all"></i> Process All
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Document</th>
                            <th>Requested</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $pending = [
                            ['Pedro Garcia',  'Barangay Clearance',   '2h ago'],
                            ['Nena Torres',   'Indigency Certificate','4h ago'],
                            ['Romy Salazar',  'Residency Certificate','Yesterday'],
                            ['Divina Flores', 'Business Clearance',  'Yesterday'],
                            ['Jose Castillo', 'Good Moral Cert.',    '2 days ago'],
                        ];
                        @endphp
                        @foreach($pending as $p)
                        <tr>
                            <td style="font-weight:600;font-size:13px;">{{ $p[0] }}</td>
                            <td style="font-size:12.5px;color:#64748b;">{{ $p[1] }}</td>
                            <td style="font-size:12px;color:#94a3b8;">{{ $p[2] }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-success" style="border-radius:6px;padding:3px 8px;font-size:12px;" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;font-size:12px;" title="Decline">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── CUSTOM LETTER + PRINT PREVIEW ── --}}
<div class="section-heading">Quick Actions</div>
<div class="row g-3">

    <div class="col-lg-5">
        <div class="form-card h-100">
            <div class="fc-heading"><i class="bi bi-pencil-square me-2 text-primary"></i>Generate Custom Letter / Document</div>
            <div class="fc-sub">Fill in the fields below to produce a custom barangay document.</div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Resident Name</label>
                <input type="text" class="form-control" style="font-size:13.5px;border-radius:8px;" placeholder="Search or type resident name…">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Document Type</label>
                <select class="form-select" style="font-size:13.5px;border-radius:8px;">
                    <option value="">— Select document type —</option>
                    <option>Indigency Certificate</option>
                    <option>Residency Certificate</option>
                    <option>Barangay Clearance</option>
                    <option>Good Moral Character Certificate</option>
                    <option>Business Clearance</option>
                    <option>Custom Letter</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Purpose</label>
                <input type="text" class="form-control" style="font-size:13.5px;border-radius:8px;" placeholder="e.g. For employment, scholarship, etc.">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12.5px;font-weight:600;">Additional Notes <span style="font-weight:400;color:#94a3b8;">(optional)</span></label>
                <textarea class="form-control" rows="3" style="font-size:13px;border-radius:8px;resize:none;" placeholder="Any special instructions or inclusions…"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2"
                        style="border-radius:8px;font-size:13.5px;font-weight:600;padding:10px;">
                    <i class="bi bi-file-earmark-plus"></i> Generate Document
                </button>
                <button class="btn btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius:8px;font-size:13px;padding:10px 14px;" title="Clear form">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="preview-panel h-100">
            <div class="preview-header">
                <i class="bi bi-eye-fill text-primary"></i>
                <span class="ph-title">Print-Ready Preview</span>
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                        style="border-radius:7px;font-size:12px;font-weight:600;">
                    <i class="bi bi-printer-fill"></i> Print
                </button>
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 ms-1"
                        style="border-radius:7px;font-size:12px;font-weight:600;">
                    <i class="bi bi-download"></i> PDF
                </button>
            </div>
            <div class="preview-body">
                <div class="doc-paper">
                    <div class="watermark">PREVIEW</div>
                    <div class="dp-letterhead">
                        <div class="dp-gov">Republic of the Philippines · City of Sample · District I</div>
                        <div class="dp-brgy">BARANGAY UNO</div>
                        <div style="font-size:10.5px;color:#64748b;">Office of the Punong Barangay</div>
                    </div>
                    <div class="dp-title">Barangay Clearance</div>
                    <div class="dp-body">
                        <p>TO WHOM IT MAY CONCERN:</p>
                        <p>This is to certify that <strong>JUAN DELA CRUZ</strong>, of legal age, Filipino citizen, and a bonafide resident of <strong>123 Mabini Street, Purok 1, Barangay Uno</strong>, is personally known to this office to be of good moral character and has no derogatory record on file with the Barangay.</p>
                        <p>This certification is issued upon the request of the above-named person for <strong>employment purposes</strong> and for whatever legal purpose it may serve.</p>
                        <p>Issued this <strong>24th day of February 2025</strong> at Barangay Uno.</p>
                    </div>
                    <div class="dp-sig">
                        <div class="dp-sig-block">
                            <div>Requested by:</div>
                            <div class="sig-name">JUAN DELA CRUZ</div>
                            <div style="font-size:10.5px;color:#64748b;">Requesting Party</div>
                        </div>
                        <div class="dp-sig-block">
                            <div>Certified by:</div>
                            <div class="sig-name">HON. SAMPLE CAPTAIN</div>
                            <div style="font-size:10.5px;color:#64748b;">Punong Barangay</div>
                        </div>
                    </div>
                    <div style="margin-top:20px;font-size:10px;color:#9ca3af;text-align:right;">
                        Control No.: <strong>BRG-2025-0842</strong> &nbsp;·&nbsp; OR No.: __________ &nbsp;·&nbsp; Fee: ₱50.00
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
{{-- No charts on this page. Add JS here as functionality is wired up. --}}
@endsection