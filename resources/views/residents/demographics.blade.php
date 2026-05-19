{{--
    residents/demographics.blade.php
    Route: GET /residents/demographics  →  route('residents.demographics')
--}}
@extends('layouts.app')

@section('title', 'Resident Demographics – Barangay Management System')
@section('page-title', 'Demographic Statistics')

@section('styles')
<style>
    .si-blue   { background: #eff6ff; color: #1a56db; }
    .si-green  { background: #f0fdf4; color: #16a34a; }
    .si-red    { background: #fef2f2; color: #dc2626; }
    .si-amber  { background: #fffbeb; color: #d97706; }
    .si-purple { background: #faf5ff; color: #7c3aed; }
    .si-cyan   { background: #f0fdfa; color: #0d9488; }

    .stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all .2s;
    }
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .stat-label {
        font-size: 12.5px;
        color: #64748b;
        font-weight: 500;
        margin-top: 4px;
    }

    .stat-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 6px;
    }

    .breakdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .breakdown-item:last-child {
        border-bottom: none;
    }

    .breakdown-label {
        font-size: 13px;
        color: #0f172a;
        font-weight: 500;
    }

    .breakdown-value {
        font-size: 14px;
        font-weight: 700;
        color: #1a56db;
    }

    .progress-bar-wrapper {
        flex: 1;
        margin: 0 12px;
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #1a56db, #0ea5e9);
        border-radius: 2px;
    }

    .chart-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
    }

    .card-heading {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .card-sub {
        font-size: 12px;
        color: #64748b;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .header-info h5 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .header-info p {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }

    .section-heading {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        margin-top: 28px;
        margin-bottom: 12px;
    }

    .export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #1a56db;
        cursor: pointer;
        transition: all .2s;
        text-decoration: none;
    }

    .export-btn:hover {
        background: #eff6ff;
        border-color: #1a56db;
        color: #1a56db;
    }

    .gender-icon-male { color: #3b82f6; }
    .gender-icon-female { color: #ec4899; }
</style>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-info">
        <h5>Demographic Statistics</h5>
        <p>Overview of resident demographics and population statistics</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('residents.exportPDF') }}" class="export-btn">
            <i class="bi bi-file-pdf"></i> Export as PDF
        </a>
        <a href="{{ route('residents.index') }}" class="export-btn" style="color: #64748b; border-color: #e2e8f0;">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Overall Statistics --}}
<div class="section-heading">Overview</div>
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-value">{{ $stats['totalResidents'] }}</div>
                <div class="stat-label">Total Residents</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-green">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-value">{{ $stats['residencyStats']['Active']->count ?? 0 }}</div>
                <div class="stat-label">Active Residents</div>
                <div class="stat-sub">
                    @if($stats['totalResidents'] > 0)
                        {{ round(($stats['residencyStats']['Active']->count ?? 0) / $stats['totalResidents'] * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-red">
                <i class="bi bi-person-dash-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-value">{{ $stats['residencyStats']['Deceased']->count ?? 0 }}</div>
                <div class="stat-label">Deceased</div>
                <div class="stat-sub">
                    @if($stats['totalResidents'] > 0)
                        {{ round(($stats['residencyStats']['Deceased']->count ?? 0) / $stats['totalResidents'] * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-amber">
                <i class="bi bi-person-walking"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-value">{{ $stats['residencyStats']['Transferred']->count ?? 0 }}</div>
                <div class="stat-label">Transferred</div>
                <div class="stat-sub">
                    @if($stats['totalResidents'] > 0)
                        {{ round(($stats['residencyStats']['Transferred']->count ?? 0) / $stats['totalResidents'] * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gender Distribution --}}
<div class="section-heading">Gender Distribution</div>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="chart-card">
            <div class="card-heading">By Gender</div>
            <div class="card-sub mb-3">Population breakdown</div>
            <div>
                @forelse($stats['genderStats'] as $gender => $stat)
                    @php
                        $percentage = $stats['totalResidents'] > 0 ? ($stat->count / $stats['totalResidents'] * 100) : 0;
                    @endphp
                    <div class="breakdown-item">
                        <div class="d-flex align-items-center gap-2">
                            @if($gender === 'Male')
                                <i class="bi bi-person-fill gender-icon-male"></i>
                            @elseif($gender === 'Female')
                                <i class="bi bi-person-fill gender-icon-female"></i>
                            @else
                                <i class="bi bi-person-fill" style="color: #6b7280;"></i>
                            @endif
                            <span class="breakdown-label">{{ $gender }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-bar-wrapper" style="width: 80px;">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="breakdown-value">{{ $stat->count }}</span>
                            <span style="color: #94a3b8; font-size: 12px; width: 40px; text-align: right;">{{ round($percentage, 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="chart-card">
            <div class="card-heading">Residency Status</div>
            <div class="card-sub mb-3">Population by residency</div>
            <div>
                @forelse($stats['residencyStats'] as $status => $stat)
                    @php
                        $percentage = $stats['totalResidents'] > 0 ? ($stat->count / $stats['totalResidents'] * 100) : 0;
                        if ($status === 'Active') $icon = 'bi-person-check-fill'; 
                        elseif ($status === 'Deceased') $icon = 'bi-person-dash-fill';
                        else $icon = 'bi-person-walking';
                    @endphp
                    <div class="breakdown-item">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $icon }}" style="color: #64748b;"></i>
                            <span class="breakdown-label">{{ $status }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-bar-wrapper" style="width: 80px;">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="breakdown-value">{{ $stat->count }}</span>
                            <span style="color: #94a3b8; font-size: 12px; width: 40px; text-align: right;">{{ round($percentage, 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Age Groups & Voter Status --}}
<div class="section-heading">Age & Voter Information</div>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="chart-card">
            <div class="card-heading">Age Group Breakdown</div>
            <div class="card-sub mb-3">Population distribution by age</div>
            <div>
                @php
                    $ageGroups = [
                        '0-12' => 'Children (0-12)',
                        '13-17' => 'Youth (13-17)',
                        '18-59' => 'Adults (18-59)',
                        '60+' => 'Seniors (60+)',
                    ];
                @endphp
                @forelse($ageGroups as $group => $label)
                    @php
                        $count = $stats['ageGroupStats'][$group] ?? 0;
                        $percentage = $stats['totalResidents'] > 0 ? ($count / $stats['totalResidents'] * 100) : 0;
                    @endphp
                    <div class="breakdown-item">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar3" style="color: #64748b;"></i>
                            <span class="breakdown-label">{{ $label }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-bar-wrapper" style="width: 80px;">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="breakdown-value">{{ $count }}</span>
                            <span style="color: #94a3b8; font-size: 12px; width: 40px; text-align: right;">{{ round($percentage, 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="chart-card">
            <div class="card-heading">Voter Status</div>
            <div class="card-sub mb-3">Voter registration status</div>
            <div>
                @forelse($stats['voterStats'] as $status => $stat)
                    @php
                        $percentage = $stats['totalResidents'] > 0 ? ($stat->count / $stats['totalResidents'] * 100) : 0;
                        if ($status === 'Registered') $icon = 'bi-check-circle-fill';
                        elseif ($status === 'Unregistered') $icon = 'bi-x-circle-fill';
                        else $icon = 'bi-exclamation-circle-fill';
                    @endphp
                    <div class="breakdown-item">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $icon }}" style="color: #64748b;"></i>
                            <span class="breakdown-label">{{ $status }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-bar-wrapper" style="width: 80px;">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="breakdown-value">{{ $stat->count }}</span>
                            <span style="color: #94a3b8; font-size: 12px; width: 40px; text-align: right;">{{ round($percentage, 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Civil Status --}}
<div class="section-heading">Civil Status</div>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="chart-card">
            <div class="card-heading">Population by Civil Status</div>
            <div class="card-sub mb-3">Marital status breakdown</div>
            <div>
                @php
                    $civilStatuses = ['Single', 'Married', 'Widowed', 'Separated', 'Divorced'];
                @endphp
                @forelse($civilStatuses as $status)
                    @php
                        $count = $stats['civilStats'][$status]->count ?? 0;
                        $percentage = $stats['totalResidents'] > 0 ? ($count / $stats['totalResidents'] * 100) : 0;
                    @endphp
                    <div class="breakdown-item">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-heart-fill" style="color: #64748b;"></i>
                            <span class="breakdown-label">{{ $status }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-bar-wrapper" style="width: 80px;">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="breakdown-value">{{ $count }}</span>
                            <span style="color: #94a3b8; font-size: 12px; width: 40px; text-align: right;">{{ round($percentage, 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
