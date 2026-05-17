@extends('layouts.app')

@section('title', 'Households Management')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Households Management</h1>
                <a href="{{ route('households.create') }}" class="btn btn-primary" style="border-radius:8px;">
                    <i class="bi bi-plus-circle"></i> Register Household
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body text-center p-4">
                    <div style="font-size:28px;color:#1a56db;margin-bottom:8px;">
                        <i class="bi bi-houses"></i>
                    </div>
                    <h3 class="fw-bold" style="color:#1e293b;margin-bottom:4px;">{{ $statistics['total_households'] }}</h3>
                    <p style="color:#94a3b8;font-size:13px;margin:0;">Total Households</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body text-center p-4">
                    <div style="font-size:28px;color:#10b981;margin-bottom:8px;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="fw-bold" style="color:#1e293b;margin-bottom:4px;">{{ $statistics['total_residents'] }}</h3>
                    <p style="color:#94a3b8;font-size:13px;margin:0;">Total Residents</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body text-center p-4">
                    <div style="font-size:28px;color:#f59e0b;margin-bottom:8px;">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h3 class="fw-bold" style="color:#1e293b;margin-bottom:4px;">{{ $statistics['avg_family_size'] }}</h3>
                    <p style="color:#94a3b8;font-size:13px;margin:0;">Avg Family Size</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body text-center p-4">
                    <div style="font-size:28px;color:#ef4444;margin-bottom:8px;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="fw-bold" style="color:#1e293b;margin-bottom:4px;">{{ $statistics['voters_count'] }}</h3>
                    <p style="color:#94a3b8;font-size:13px;margin:0;">Registered Voters</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('households.statistics') }}" class="btn btn-outline-info">
                <i class="bi bi-graph-up"></i> View Detailed Statistics
            </a>
        </div>
    </div>

    {{-- Households Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-0">
                    @if($households->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color:#f1f5f9;border-bottom:2px solid #e2e8f0;">
                                    <tr>
                                        <th style="color:#475569;font-weight:600;padding:12px;">House #</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Street</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Purok</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Head</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Family Size</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Residents</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($households as $household)
                                        <tr>
                                            <td style="padding:12px;"><strong>{{ $household->house_number }}</strong></td>
                                            <td style="padding:12px;">{{ $household->street }}</td>
                                            <td style="padding:12px;">
                                                <span class="badge" style="background-color:#e0e7ff;color:#1a56db;">
                                                    {{ $household->purok?->purok_name }}
                                                </span>
                                            </td>
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
                                            <td style="padding:12px;">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('households.show', $household->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('households.edit', $household->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('households.destroy', $household->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this household?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer" style="background-color:#f8fafc;border-top:1px solid #e2e8f0;padding:12px;">
                            {{ $households->links() }}
                        </div>
                    @else
                        <div style="padding:40px;text-align:center;">
                            <div style="font-size:48px;color:#cbd5e1;margin-bottom:12px;">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <p style="color:#94a3b8;margin-bottom:20px;">No households registered yet.</p>
                            <a href="{{ route('households.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create First Household
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
