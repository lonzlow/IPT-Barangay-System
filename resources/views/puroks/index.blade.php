@extends('layouts.app')

@section('title', 'Puroks Management')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Puroks Management</h1>
                <a href="{{ route('puroks.create') }}" class="btn btn-primary" style="border-radius:8px;">
                    <i class="bi bi-plus-circle"></i> Create Purok
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body text-center p-4">
                    <div style="font-size:28px;color:#1a56db;margin-bottom:8px;">
                        <i class="bi bi-map"></i>
                    </div>
                    <h3 class="fw-bold" style="color:#1e293b;margin-bottom:4px;">{{ $statistics['total_puroks'] }}</h3>
                    <p style="color:#94a3b8;font-size:13px;margin:0;">Total Puroks</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body text-center p-4">
                    <div style="font-size:28px;color:#10b981;margin-bottom:8px;">
                        <i class="bi bi-houses"></i>
                    </div>
                    <h3 class="fw-bold" style="color:#1e293b;margin-bottom:4px;">
                        @php
                            $totalHouseholds = \App\Models\Household::count();
                        @endphp
                        {{ $totalHouseholds }}
                    </h3>
                    <p style="color:#94a3b8;font-size:13px;margin:0;">Total Households</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Puroks Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-0">
                    @if($puroks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color:#f1f5f9;border-bottom:2px solid #e2e8f0;">
                                    <tr>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Purok Name</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Description</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Leader</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Households</th>
                                        <th style="color:#475569;font-weight:600;padding:12px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($puroks as $purok)
                                        <tr>
                                            <td style="padding:12px;"><strong>{{ $purok->purok_name }}</strong></td>
                                            <td style="padding:12px;">{{ Str::limit($purok->description, 50) ?? 'N/A' }}</td>
                                            <td style="padding:12px;">
                                                {{ $purok->leader?->first_name ?? 'None' }} 
                                                {{ $purok->leader?->last_name ?? '' }}
                                            </td>
                                            <td style="padding:12px;">
                                                <span class="badge" style="background-color:#e0e7ff;color:#1a56db;">
                                                    {{ $purok->households_count }}
                                                </span>
                                            </td>
                                            <td style="padding:12px;">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('puroks.show', $purok->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('puroks.edit', $purok->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('puroks.destroy', $purok->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this purok?')">
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
                            {{ $puroks->links() }}
                        </div>
                    @else
                        <div style="padding:40px;text-align:center;">
                            <div style="font-size:48px;color:#cbd5e1;margin-bottom:12px;">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <p style="color:#94a3b8;margin-bottom:20px;">No puroks created yet.</p>
                            <a href="{{ route('puroks.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create First Purok
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
