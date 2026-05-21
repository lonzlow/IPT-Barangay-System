@extends('layouts.app')

@section('title', 'Household Details')
@section('page-title', 'Household Details')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">{{ $household->house_number }} {{ $household->street }}</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">{{ $household->purok?->purok_name ?? 'No Purok assigned' }}</p>
    </div>
    <div class="d-flex gap-2">
        @can('households.manage')
            <a href="{{ route('households.edit', $household) }}" class="btn btn-warning" style="border-radius:8px;">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endcan
        <a href="{{ route('households.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['family_size'] }}</div>
            <div class="stat-label">Family Size</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['registered_voters'] }}</div>
            <div class="stat-label">Registered Voters</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['active_residents'] }}</div>
            <div class="stat-label">Active Residents</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4" style="border:1px solid #e2e8f0;border-radius:12px;">
            <div class="card-header" style="background-color:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <strong>Household Info</strong>
            </div>
            <div class="card-body">
                <p class="mb-2"><span class="text-muted">Purok:</span> {{ $household->purok?->purok_name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="text-muted">Address:</span> {{ $household->house_number }} {{ $household->street }}</p>
                <p class="mb-0">
                    <span class="text-muted">Head:</span>
                    @if($household->head_resident)
                        {{ trim(collect([$household->head_resident->first_name, $household->head_resident->middle_name, $household->head_resident->last_name, $household->head_resident->suffix])->filter()->implode(' ')) }}
                    @else
                        Unassigned
                    @endif
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="table-card">
            <div class="table-header">
                <div class="heading">Members</div>
            </div>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Voter Status</th>
                        <th>Residency</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($household->residents as $resident)
                        <tr>
                            <td>{{ trim(collect([$resident->first_name, $resident->middle_name, $resident->last_name, $resident->suffix])->filter()->implode(' ')) }}</td>
                            <td>
                                <span class="badge {{ $resident->voter_status === 'Registered' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $resident->voter_status }}
                                </span>
                            </td>
                            <td>{{ $resident->residency_status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No residents assigned to this household.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
