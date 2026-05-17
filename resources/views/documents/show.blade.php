@extends('layouts.app')

@section('title', 'View Document - ' . $document->reference_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="fw-bold" style="font-size:28px;">{{ $document->template->name }}</h1>
                    <p class="text-secondary mb-0">
                        <small>Reference: <strong>{{ $document->reference_number }}</strong></small>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('documents.downloadPdf', $document->id) }}" class="btn btn-primary" style="border-radius:8px;">
                        <i class="bi bi-download"></i> Download PDF
                    </a>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Document Details Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <small style="color:#94a3b8;font-weight:600;">Resident</small>
                                <p class="fw-600" style="color:#1e293b;">{{ $document->resident->first_name }} {{ $document->resident->last_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <small style="color:#94a3b8;font-weight:600;">Issued Date</small>
                                <p class="fw-600" style="color:#1e293b;">{{ $document->issued_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <small style="color:#94a3b8;font-weight:600;">Valid Until</small>
                                <p class="fw-600" style="color:#1e293b;">
                                    @if($document->valid_until)
                                        {{ $document->valid_until->format('M d, Y') }}
                                    @else
                                        <span class="badge bg-secondary">No Expiration</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <small style="color:#94a3b8;font-weight:600;">Status</small>
                                <p class="fw-600">
                                    @if($document->status === 'Issued')
                                        <span class="badge bg-success">{{ $document->status }}</span>
                                    @elseif($document->status === 'Revoked')
                                        <span class="badge bg-danger">{{ $document->status }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ $document->status }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Document Preview --}}
    <div class="row">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background:#f9fafb;border-bottom:1px solid #e2e8f0;border-radius:12px 12px 0 0;">
                    <div class="d-flex align-items-center justify-content-between p-3">
                        <h6 class="fw-600 mb-0" style="color:#1e293b;">Document Preview</h6>
                        <a href="{{ route('documents.downloadPdf', $document->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-printer"></i> Print
                        </a>
                    </div>
                </div>
                <div class="card-body p-4" style="background:#fff;">
                    <div style="background:#f9fafb;padding:30px;border-radius:8px;border:1px solid #e2e8f0;min-height:600px;">
                        {!! $document->rendered_html !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                @if($document->status === 'Issued')
                    <form action="{{ route('documents.update', $document->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="Revoked">
                        <input type="hidden" name="issued_by" value="{{ $document->issued_by }}">
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to revoke this document?');">
                            <i class="bi bi-x-circle"></i> Revoke Document
                        </button>
                    </form>
                @endif
                <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('documents.destroy', $document->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this document?');">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
