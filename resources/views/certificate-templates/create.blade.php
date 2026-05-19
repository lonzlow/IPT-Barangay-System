@extends('layouts.app')

@section('title', 'Create Certificate Template')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Create Certificate Template</h1>
                <a href="{{ route('certificate-templates.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <form action="{{ route('certificate-templates.store') }}" method="POST">
                        @csrf

                        {{-- Template Name --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-bookmark"></i> Template Name
                            </label>
                            <input type="text" name="name" class="form-control form-control-lg" style="border-radius:8px;" placeholder="e.g., Barangay Indigency Certificate" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-file-text"></i> Description
                            </label>
                            <textarea name="description" class="form-control" style="border-radius:8px;font-size:14px;" rows="2" placeholder="Brief description of this certificate"></textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Template HTML --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-code-square"></i> HTML Template
                            </label>
                            <textarea name="template_html" class="form-control" style="border-radius:8px;font-size:12px;font-family:monospace;" rows="10" required placeholder="HTML template with {{placeholders}}"></textarea>
                            <small class="d-block mt-2" style="color:#64748b;">
                                Available placeholders: {{resident_name}}, {{first_name}}, {{last_name}}, {{age}}, {{gender}}, {{email}}, {{contact_number}}, {{household_address}}, {{current_date}}, {{current_date_long}}, {{issued_by}}, {{reference_number}}
                            </small>
                            @error('template_html')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Validity Days --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-calendar"></i> Validity Period (Days)
                            </label>
                            <input type="number" name="validity_days" class="form-control form-control-lg" style="border-radius:8px;" placeholder="e.g., 180" min="1">
                            <small style="color:#94a3b8;">Leave empty for documents that don't expire</small>
                            @error('validity_days')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Active Status --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked style="width:48px;height:28px;">
                                <label class="form-check-label fw-600" for="is_active" style="color:#1e293b;">
                                    <i class="bi bi-check-circle"></i> Active (Make available for document issuance)
                                </label>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex gap-2 pt-3">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-check-circle"></i> Create Template
                            </button>
                            <a href="{{ route('certificate-templates.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Help Section --}}
        <div class="col-lg-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#f9fafb;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-lightbulb"></i> Template Guide
                    </h6>
                    <div style="font-size:12px;line-height:1.8;color:#64748b;">
                        <p><strong>How to create a template:</strong></p>
                        <ol class="ps-3">
                            <li>Enter a unique template name</li>
                            <li>Add HTML markup with placeholders</li>
                            <li>Use {{placeholder_name}} syntax</li>
                            <li>Set validity period (optional)</li>
                            <li>Activate template</li>
                        </ol>
                        <p class="mt-3"><strong>Supported placeholders:</strong></p>
                        <ul class="ps-3" style="font-size:11px;">
                            <li>{{resident_name}} - Full name</li>
                            <li>{{first_name}} - First name only</li>
                            <li>{{age}}, {{gender}}, {{email}}</li>
                            <li>{{household_address}} - Complete address</li>
                            <li>{{current_date}} - Formatted date</li>
                            <li>{{issued_by}} - Officer name</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
