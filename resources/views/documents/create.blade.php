@extends('layouts.app')

@section('title', 'Issue New Document')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Issue New Document</h1>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Documents
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-body p-4">
                    <form action="{{ route('documents.store') }}" method="POST">
                        @csrf

                        {{-- Resident Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-person"></i> Resident
                            </label>
                            <select name="resident_id" class="form-select form-select-lg" style="border-radius:8px;" required>
                                <option value="">-- Select Resident --</option>
                                @foreach($residents as $resident)
                                    <option value="{{ $resident->id }}">
                                        {{ $resident->first_name }} {{ $resident->last_name }} 
                                        ({{ $resident->household?->purok?->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('resident_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Document Template Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-file-text"></i> Document Type
                            </label>
                            <select name="document_template_id" class="form-select form-select-lg" style="border-radius:8px;" required>
                                <option value="">-- Select Document Type --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" data-description="{{ $template->description }}">
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('document_template_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small id="template-desc" class="d-block mt-2" style="color:#64748b;"></small>
                        </div>

                        {{-- Purpose (Optional) --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-card-text"></i> Purpose of Issuance (Optional)
                            </label>
                            <textarea name="purpose" class="form-control" style="border-radius:8px;font-size:14px;" rows="3" placeholder="e.g., For Employment, For Travel, For Education, etc."></textarea>
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
                                   value="{{ auth()->user()->name }}" required>
                            @error('issued_by')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex gap-2 pt-3">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-check-circle"></i> Issue Document
                            </button>
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Help Section --}}
        <div class="col-lg-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#f9fafb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-lightbulb"></i> Tips
                    </h6>
                    <div style="font-size:13px;line-height:1.8;color:#64748b;">
                        <p><strong>Before issuing:</strong></p>
                        <ul class="ps-3">
                            <li>Verify resident details are correct</li>
                            <li>Select the appropriate document type</li>
                            <li>Note the purpose of issuance if applicable</li>
                            <li>Ensure the issuing officer name is correct</li>
                        </ul>
                        <p class="mt-3"><strong>After issuance:</strong></p>
                        <ul class="ps-3">
                            <li>Document will be automatically rendered with resident data</li>
                            <li>You can print or download as PDF</li>
                            <li>Reference number is auto-generated</li>
                            <li>Validity period depends on document type</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const templateSelect = document.querySelector('select[name="document_template_id"]');
        const descElement = document.getElementById('template-desc');

        templateSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const desc = option.dataset.description;
            descElement.textContent = desc || '';
        });
    });
</script>
@endsection
