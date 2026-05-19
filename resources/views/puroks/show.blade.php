@extends('layouts.app')

@section('title', 'Purok Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">{{ $purok->purok_name }}</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('puroks.edit', $purok->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('puroks.index') }}" class="btn btn-outline-secondary">
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
                        <i class="bi bi-map"></i> Purok Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Purok Name</strong></p>
                            <p style="color:#1e293b;font-size:16px;">{{ $purok->purok_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Leader</strong></p>
                            <p style="color:#1e293b;font-size:16px;">
                                {{ $purok->leader?->first_name ?? 'Unassigned' }} 
                                {{ $purok->leader?->last_name ?? '' }}
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Description</strong></p>
                            <p style="color:#1e293b;font-size:14px;">{{ $purok->description ?? 'No description provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Households --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-houses"></i> Households ({{ $householdCount }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($householdCount > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                    <tr>
                                        <th style="color:#475569;font-weight:600;padding:12px;">House #</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Street</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Head</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Family Size</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Members</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purok->households as $household)
                                        <tr>
                                            <td style="padding:12px;">
                                                <a href="{{ route('households.show', $household->id) }}" style="color:#1a56db;text-decoration:none;">
                                                    <strong>{{ $household->house_number }}</strong>
                                                </a>
                                            </td>
                                            <td style="padding:12px;">{{ $household->street }}</td>
                                            <td style="padding:12px;">
                                                {{ $household->head_resident?->first_name ?? 'Unassigned' }}
                                                {{ $household->head_resident?->last_name ?? '' }}
                                            </td>
                                            <td style="padding:12px;">{{ $household->family_size }}</td>
                                            <td style="padding:12px;">
                                                <span class="badge" style="background-color:#dbeafe;color:#0369a1;">
                                                    {{ $household->residents()->count() }}
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
                            No households in this purok yet.
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
                        <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Households</strong></p>
                        <h4 style="color:#1e293b;margin-bottom:0;">{{ $householdCount }}</h4>
                    </div>
                    <div class="mb-3">
                        <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Total Residents</strong></p>
                        <h4 style="color:#1e293b;margin-bottom:0;">{{ $residentCount }}</h4>
                    </div>
                    <div>
                        <p style="color:#94a3b8;font-size:12px;margin-bottom:4px;"><strong>Registered Voters</strong></p>
                        <h4 style="color:#1e293b;margin-bottom:0;">{{ $voterCount }}</h4>
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
                        <a href="{{ route('puroks.edit', $purok->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit Purok
                        </a>
                        <form action="{{ route('puroks.destroy', $purok->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Delete this purok?')">
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
