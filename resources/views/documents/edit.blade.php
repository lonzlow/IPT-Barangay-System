@extends('layouts.app')

@section('title', 'Edit Document')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Edit Document</h1>
                <a href="{{ route('documents.show', $document->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <form action="{{ route('documents.update', $document->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Purpose --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-card-text"></i> Purpose of Issuance
                            </label>
                            <textarea name="purpose" class="form-control" style="border-radius:8px;font-size:14px;" rows="3">{{ $document->purpose }}</textarea>
                            @error('purpose')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Issued By --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-pencil-square"></i> Issued By
                            </label>
                            <input type="text" name="issued_by" class="form-control form-control-lg" style="border-radius:8px;" 
                                   value="{{ $document->issued_by }}" required>
                            @error('issued_by')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-info-circle"></i> Status
                            </label>
                            <select name="status" class="form-select form-select-lg" style="border-radius:8px;" required>
                                <option value="Issued" {{ $document->status === 'Issued' ? 'selected' : '' }}>Issued</option>
                                <option value="Revoked" {{ $document->status === 'Revoked' ? 'selected' : '' }}>Revoked</option>
                                <option value="Expired" {{ $document->status === 'Expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                            @error('status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex gap-2 pt-3">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                            <a href="{{ route('documents.show', $document->id) }}" class="btn btn-outline-secondary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Document Info Sidebar --}}
        <div class="col-lg-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#f9fafb;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-file-text"></i> Document Information
                    </h6>
                    <div style="font-size:13px;line-height:2;">
                        <p>
                            <strong>Reference:</strong><br>
                            <span style="color:#64748b;">{{ $document->reference_number }}</span>
                        </p>
                        <p>
                            <strong>Resident:</strong><br>
                            <span style="color:#64748b;">{{ $document->resident->first_name }} {{ $document->resident->last_name }}</span>
                        </p>
                        <p>
                            <strong>Document Type:</strong><br>
                            <span style="color:#64748b;">{{ $document->template->name }}</span>
                        </p>
                        <p>
                            <strong>Issued Date:</strong><br>
                            <span style="color:#64748b;">{{ $document->issued_date->format('F d, Y') }}</span>
                        </p>
                        <p>
                            <strong>Valid Until:</strong><br>
                            <span style="color:#64748b;">
                                @if($document->valid_until)
                                    {{ $document->valid_until->format('F d, Y') }}
                                @else
                                    No expiration
                                @endif
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
