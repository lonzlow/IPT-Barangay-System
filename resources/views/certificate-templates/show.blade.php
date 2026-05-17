@extends('layouts.app')

@section('title', $template->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">{{ $template->name }}</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('certificate-templates.edit', $template->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('certificate-templates.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Template Details --}}
            <div class="card mb-4" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e293b;">Template Information</h6>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <small style="color:#94a3b8;font-weight:600;">Template Name</small>
                            <p style="color:#1e293b;margin:0;">{{ $template->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small style="color:#94a3b8;font-weight:600;">Status</small>
                            <p style="margin:0;">
                                @if($template->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small style="color:#94a3b8;font-weight:600;">Validity Period</small>
                            <p style="color:#1e293b;margin:0;">{{ $template->validity_days ?? 'No expiration' }} days</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small style="color:#94a3b8;font-weight:600;">Documents Issued</small>
                            <p style="color:#1e293b;margin:0;">{{ $template->documents()->count() }} total</p>
                        </div>
                    </div>

                    @if($template->description)
                        <div class="mb-4">
                            <small style="color:#94a3b8;font-weight:600;">Description</small>
                            <p style="color:#1e293b;">{{ $template->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Template HTML --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background:#f9fafb;border-bottom:1px solid #e2e8f0;border-radius:12px 12px 0 0;padding:16px;">
                    <h6 class="fw-600 mb-0" style="color:#1e293b;">Template Code</h6>
                </div>
                <div class="card-body p-4">
                    <pre style="background:#f5f5f5;padding:12px;border-radius:6px;border:1px solid #e2e8f0;overflow-x:auto;font-size:12px;line-height:1.5;"><code>{{ $template->template_html }}</code></pre>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Stats --}}
            <div class="card mb-4" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-bar-chart"></i> Statistics
                    </h6>
                    <div style="font-size:13px;line-height:2;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="color:#64748b;">Documents Issued</span>
                            <strong style="color:#1e293b;">{{ $template->documents()->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="color:#64748b;">Created</span>
                            <strong style="color:#1e293b;">{{ $template->created_at->format('M d, Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="color:#64748b;">Last Updated</span>
                            <strong style="color:#1e293b;">{{ $template->updated_at->format('M d, Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Available Placeholders --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#f9fafb;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-tags"></i> Available Placeholders
                    </h6>
                    <div style="font-size:12px;line-height:1.8;color:#64748b;">
                        <ul class="ps-3" style="margin:0;">
                            <li>{{resident_name}}</li>
                            <li>{{first_name}}, {{last_name}}</li>
                            <li>{{age}}, {{gender}}</li>
                            <li>{{email}}, {{contact_number}}</li>
                            <li>{{household_address}}</li>
                            <li>{{current_date}}</li>
                            <li>{{current_date_long}}</li>
                            <li>{{issued_by}}</li>
                            <li>{{reference_number}}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
