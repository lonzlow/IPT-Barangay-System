@extends('layouts.app')

@section('title', 'Household Statistics')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Household Statistics</h1>
                <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Key Statistics --}}
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
                    <p style="color:#94a3b8;font-size:13px;margin:0;">Average Family Size</p>
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

    {{-- Detailed Statistics --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-graph-up"></i> Family Size Distribution
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p style="color:#94a3b8;margin-bottom:16px;">
                        <strong>Largest household:</strong> {{ $statistics['largest_household_size'] }} members
                    </p>
                    <p style="color:#94a3b8;margin-bottom:0;">
                        <strong>Smallest household:</strong> {{ $statistics['smallest_household_size'] }} member(s)
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-percent"></i> Voter Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p style="color:#94a3b8;margin-bottom:16px;">
                        <strong>Total Voters:</strong> {{ $statistics['voters_count'] }}
                    </p>
                    <p style="color:#94a3b8;margin-bottom:0;">
                        <strong>Voter Percentage:</strong> {{ $statistics['voters_percentage'] }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Households by Purok --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-map"></i> Households by Purok
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <tr>
                                    <th style="color:#475569;font-weight:600;padding:12px;">Purok</th>
                                    <th style="color:#475569;font-weight:600;padding:12px;text-align:center;">Households</th>
                                    <th style="color:#475569;font-weight:600;padding:12px;text-align:center;">Residents</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($householdsByPurok as $stat)
                                    <tr>
                                        <td style="padding:12px;">
                                            <strong>{{ $stat->purok_name }}</strong>
                                        </td>
                                        <td style="padding:12px;text-align:center;">
                                            <span class="badge" style="background-color:#e0e7ff;color:#1a56db;">
                                                {{ $stat->households_count }}
                                            </span>
                                        </td>
                                        <td style="padding:12px;text-align:center;">
                                            <span class="badge" style="background-color:#dcfce7;color:#166534;">
                                                {{ $stat->residents_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Voter Statistics by Purok --}}
    <div class="row">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-header" style="background-color:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-check-circle"></i> Registered Voters by Purok
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <tr>
                                    <th style="color:#475569;font-weight:600;padding:12px;">Purok</th>
                                    <th style="color:#475569;font-weight:600;padding:12px;text-align:center;">Registered Voters</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($householdsByPurok as $purok)
                                    <tr>
                                        <td style="padding:12px;">
                                            <strong>{{ $purok->purok_name }}</strong>
                                        </td>
                                        <td style="padding:12px;text-align:center;">
                                            <span class="badge" style="background-color:#fef3c7;color:#b45309;">
                                                {{ $purok->registered_voters_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="padding:12px;text-align:center;color:#94a3b8;">
                                            No voter data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
