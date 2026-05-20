@extends('layouts.app')

@section('title', 'Blotter Details - Barangay Management System')
@section('page-title', 'Blotter Details')

@section('content')

@php
    $status = strtolower($blotter->status ?? 'pending');

    $badgeClasses = match ($status) {
        'pending' => 'bg-danger-subtle text-danger',
        'under investigation' => 'bg-warning-subtle text-warning',
        'resolved' => 'bg-success-subtle text-success',
        'referred' => 'bg-primary-subtle text-primary',
        default => 'bg-secondary-subtle text-secondary',
    };

    $complainant = $blotter->complainant_resident
        ? trim(
            $blotter->complainant_resident->first_name . ' ' .
            $blotter->complainant_resident->middle_name . ' ' .
            $blotter->complainant_resident->last_name . ' ' .
            $blotter->complainant_resident->suffix
        )
        : ($blotter->complainant_name ?? 'Unknown');

    $filedBy = $blotter->filedBy?->official?->resident
        ? trim(
            $blotter->filedBy->official->resident->first_name . ' ' .
            $blotter->filedBy->official->resident->last_name
        )
        : 'Unknown Officer';
@endphp

<div class="container-fluid py-3">

    {{-- Back Button --}}
    <div class="mb-4">
        <a href="{{ route('blotters.index') }}"
           class="btn btn-light border d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            Back to Blotter Records
        </a>
    </div>

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                <div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge {{ $badgeClasses }}">
                            {{ ucfirst($status) }}
                        </span>

                        <span class="badge bg-dark-subtle text-dark">
                            Case #: {{ $blotter->case_number }}
                        </span>
                    </div>

                    <h2 class="fw-bold mb-2">
                        Blotter Case Information
                    </h2>

                    <p class="text-muted mb-0">
                        Complete barangay blotter case overview, respondents,
                        witnesses, evidences, and incident details.
                    </p>
                </div>

                <div class="text-lg-end">
                    <div class="text-muted small">Incident Date</div>
                    <div class="fw-semibold">
                        {{ $blotter->incident_date?->format('M d, Y h:i A') ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Main Content --}}
        <div class="col-lg-8">

            {{-- Overview --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-bold mb-0">Blotter Overview</h6>
                </div>

                <div class="card-body">
                    <div class="row g-4">

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Complainant</div>
                            <div class="fw-semibold">
                                {{ $complainant }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Filed By</div>
                            <div class="fw-semibold">
                                {{ $filedBy }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Location</div>
                            <div class="fw-semibold">
                                {{ $blotter->location ?? 'No location specified' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Status</div>
                            <div>
                                <span class="badge {{ $badgeClasses }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Evidences --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-bold mb-0">Evidence Files</h6>
                </div>

                <div class="card-body">

                    @if($blotter->evidences->count())

                        <div class="row g-3">

                            @foreach($blotter->evidences as $evidence)

                                <div class="col-md-4">

                                    <div class="border rounded overflow-hidden">

                                        @if(Str::startsWith($evidence->mime_type, 'image/'))

                                            <a href="{{ asset($evidence->file_path) }}"
                                               target="_blank">

                                                <img
                                                    src="{{ asset($evidence->file_path) }}"
                                                    class="img-fluid w-100"
                                                    style="height:220px; object-fit:cover;"
                                                    alt="Evidence Image">

                                            </a>

                                        @elseif(Str::startsWith($evidence->mime_type, 'video/'))

                                            <video
                                                controls
                                                class="w-100"
                                                style="height:220px; object-fit:cover;">

                                                <source
                                                    src="{{ asset($evidence->file_path) }}"
                                                    type="{{ $evidence->mime_type }}">

                                            </video>

                                        @else

                                            <a href="{{ asset($evidence->file_path) }}"
                                               target="_blank"
                                               class="d-flex align-items-center justify-content-center text-decoration-none bg-light"
                                               style="height:220px;">
                                                <div class="text-center">
                                                    <i class="bi bi-file-earmark-text d-block mb-2" style="font-size:42px;"></i>
                                                    <span class="fw-semibold">Open File</span>
                                                </div>
                                            </a>

                                        @endif

                                        <div class="p-3">

                                            <div class="small fw-semibold mb-1">
                                                {{ strtoupper($evidence->file_category ?? 'FILE') }}
                                            </div>

                                            @if($evidence->caption)
                                                <div class="small text-muted">
                                                    {{ $evidence->caption }}
                                                </div>
                                            @endif

                                            <div class="small text-muted mt-2">
                                                {{ strtoupper($evidence->file_extension) }}
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="text-center py-4 text-muted">
                            No evidence files uploaded for this blotter.
                        </div>

                    @endif

                </div>
            </div>

            {{-- Description --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-bold mb-0">Incident Description</h6>
                </div>

                <div class="card-body">
                    @if($blotter->incident_description)
                        <p class="mb-0 text-secondary" style="white-space: pre-line;">
                            {{ $blotter->incident_description }}
                        </p>
                    @else
                        <p class="text-muted mb-0">
                            No incident description available.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Respondents --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Respondents</h6>

                    <span class="small text-muted">
                        {{ $blotter->respondents->count() }}
                        respondent{{ $blotter->respondents->count() != 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="card-body">

                    @forelse($blotter->respondents as $respondent)

                        @php
                            $resident = $respondent->respondent;

                            $respondentName = $resident
                                ? trim(
                                    $resident->first_name . ' ' .
                                    $resident->middle_name . ' ' .
                                    $resident->last_name . ' ' .
                                    $resident->suffix
                                )
                                : ($respondent->respondent_name ?? 'Unknown Respondent');
                        @endphp

                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                <h6 class="fw-semibold mb-0">
                                    {{ $respondentName }}
                                </h6>

                                @if($respondent->role)
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ ucfirst($respondent->role) }}
                                    </span>
                                @endif
                            </div>

                            @if($resident)
                                <div class="row g-2 small">
                                    <div class="col-md-6">
                                        <span class="text-muted">Gender:</span>
                                        {{ $resident->gender ?? 'N/A' }}
                                    </div>

                                    <div class="col-md-6">
                                        <span class="text-muted">Contact:</span>
                                        {{ $resident->contact_number ?? 'N/A' }}
                                    </div>

                                    <div class="col-md-6">
                                        <span class="text-muted">Email:</span>
                                        {{ $resident->email ?? 'N/A' }}
                                    </div>

                                    <div class="col-md-6">
                                        <span class="text-muted">Age:</span>
                                        {{ $resident->age ?? 'N/A' }}
                                    </div>
                                </div>
                            @endif
                        </div>

                    @empty

                        <div class="text-center py-4 text-muted">
                            No respondents linked to this blotter.
                        </div>

                    @endforelse

                </div>
            </div>

            {{-- Witnesses --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Witnesses</h6>

                    <span class="small text-muted">
                        {{ $blotter->witnesses->count() }}
                        witness{{ $blotter->witnesses->count() != 1 ? 'es' : '' }}
                    </span>
                </div>

                <div class="card-body">

                    @forelse($blotter->witnesses as $witness)

                        @php
                            $resident = $witness->resident_witness;

                            $witnessName = $resident
                                ? trim(
                                    $resident->first_name . ' ' .
                                    $resident->middle_name . ' ' .
                                    $resident->last_name . ' ' .
                                    $resident->suffix
                                )
                                : ($witness->witness_name ?? 'Unknown Witness');
                        @endphp

                        <div class="border rounded p-3 mb-3">
                            <h6 class="fw-semibold mb-2">
                                {{ $witnessName }}
                            </h6>

                            @if($resident)
                                <div class="row g-2 small">
                                    <div class="col-md-6">
                                        <span class="text-muted">Gender:</span>
                                        {{ $resident->gender ?? 'N/A' }}
                                    </div>

                                    <div class="col-md-6">
                                        <span class="text-muted">Contact:</span>
                                        {{ $resident->contact_number ?? 'N/A' }}
                                    </div>

                                    <div class="col-md-6">
                                        <span class="text-muted">Email:</span>
                                        {{ $resident->email ?? 'N/A' }}
                                    </div>

                                    <div class="col-md-6">
                                        <span class="text-muted">Age:</span>
                                        {{ $resident->age ?? 'N/A' }}
                                    </div>
                                </div>
                            @endif
                        </div>

                    @empty

                        <div class="text-center py-4 text-muted">
                            No witnesses linked to this blotter.
                        </div>

                    @endforelse

                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">

            {{-- Quick Summary --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-bold mb-0">Quick Summary</h6>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Case Number</span>
                        <span class="fw-semibold">
                            {{ $blotter->case_number }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Status</span>

                        <span class="badge {{ $badgeClasses }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Respondents</span>

                        <span class="fw-semibold">
                            {{ $blotter->respondents->count() }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Witnesses</span>

                        <span class="fw-semibold">
                            {{ $blotter->witnesses->count() }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Evidence Files</span>

                        <span class="fw-semibold">
                            {{ $blotter->evidences->count() }}
                        </span>
                    </div>

                </div>
            </div>

            {{-- Metadata --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-bold mb-0">Record Metadata</h6>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Blotter ID</div>
                        <div class="small text-break">
                            {{ $blotter->id }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Created At</div>
                        <div>
                            {{ $blotter->created_at?->format('M d, Y h:i A') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Updated At</div>
                        <div>
                            {{ $blotter->updated_at?->format('M d, Y h:i A') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small mb-1">Handled By</div>
                        <div>
                            {{ $filedBy }}
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

@endsection
