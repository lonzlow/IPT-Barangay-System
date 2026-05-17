@extends('layouts.app')

@section('title', 'Household Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Household #{{ $household->house_number }}</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('households.edit', $household->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Info --}}
        <div class="col-lg-8">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;margin-bottom:20px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-house"></i> Household Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>House Number</strong></p>
                            <p style="color:#1e293b;font-size:16px;">{{ $household->house_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Street</strong></p>
                            <p style="color:#1e293b;font-size:16px;">{{ $household->street }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Purok</strong></p>
                            <p style="color:#1e293b;font-size:16px;">
                                <span class="badge" style="background-color:#e0e7ff;color:#1a56db;">
                                    {{ $household->purok?->purok_name }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Family Size</strong></p>
                            <p style="color:#1e293b;font-size:16px;">{{ $household->family_size }} member(s)</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Head of Household</strong></p>
                            <p style="color:#1e293b;font-size:16px;">
                                {{ $household->head_resident?->first_name ?? 'Unassigned' }} 
                                {{ $household->head_resident?->last_name ?? '' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Registered</strong></p>
                            <p style="color:#1e293b;font-size:16px;">{{ $household->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Residents --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-people"></i> Household Members ({{ $household->residents()->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($household->residents()->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                    <tr>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Name</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Age</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Gender</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Voter Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($household->residents as $resident)
                                        <tr>
                                            <td style="padding:12px;">
                                                <a href="{{ route('residents.show', $resident->id) }}" style="color:#1a56db;text-decoration:none;">
                                                    <strong>{{ $resident->first_name }} {{ $resident->last_name }}</strong>
                                                </a>
                                            </td>
                                            <td style="padding:12px;">{{ $resident->age ?? 'N/A' }}</td>
                                            <td style="padding:12px;">{{ $resident->gender }}</td>
                                            <td style="padding:12px;">
                                                <span class="badge" style="background-color:{{ $resident->voter_status === 'Registered Voter' ? '#dcfce7' : '#fecaca' }};color:{{ $resident->voter_status === 'Registered Voter' ? '#166534' : '#dc2626' }};">
                                                    {{ $resident->voter_status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding:40px;text-align:center;color:#94a3b8;">
                            <i class="bi bi-inbox" style="font-size:32px;margin-bottom:12px;display:block;"></i>
                            No residents assigned to this household yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Statistics Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;margin-bottom:20px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-graph-up"></i> Statistics
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Members</strong></p>
                        <h4 style="color:#1e293b;margin-bottom:0;">{{ $household->residents()->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Voters</strong></p>
                        <h4 style="color:#1e293b;margin-bottom:0;">{{ $household->residents()->where('voter_status', 'Registered Voter')->count() }}</h4>
                    </div>
                    <div>
                        <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Purok Leader</strong></p>
                        <p style="color:#1e293b;">{{ $household->purok?->leader?->first_name ?? 'None' }} {{ $household->purok?->leader?->last_name ?? '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-gear"></i> Actions
                    </h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('households.edit', $household->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit Household
                        </a>
                        <form action="{{ route('households.destroy', $household->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Delete this household?')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
