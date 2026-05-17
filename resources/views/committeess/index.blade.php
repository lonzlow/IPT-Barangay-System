{{--
    committees.blade.php
    Route: GET /committees  →  route('committees.index')
--}}
@extends('layouts.app')

@section('title', 'Committee Management – Barangay Management System')
@section('page-title', 'Committee Management')

@section('styles')
<style>
    .committee-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        overflow: hidden; transition: box-shadow .2s;
    }
    .committee-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); }

    .committee-card .cc-header {
        padding: 16px 20px 14px;
        display: flex; align-items: center; gap: 12px;
    }
    .committee-card .cc-icon {
        width: 44px; height: 44px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .committee-card .cc-name { font-size: 14px; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .committee-card .cc-chair{ font-size: 11.5px; color: #64748b; margin-top: 2px; }

    .committee-card .cc-stats {
        display: flex; border-top: 1px solid #f1f5f9;
    }
    .committee-card .cc-stat {
        flex: 1; padding: 12px 14px; text-align: center;
        border-right: 1px solid #f1f5f9;
    }
    .committee-card .cc-stat:last-child { border-right: none; }
    .committee-card .cc-stat-val { font-size: 18px; font-weight: 800; font-family: 'DM Mono',monospace; color: #0f172a; line-height: 1; }
    .committee-card .cc-stat-lbl { font-size: 10.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; margin-top: 3px; }

    .committee-card .cc-actions {
        padding: 12px 16px; border-top: 1px solid #f1f5f9;
        display: flex; gap: 6px; flex-wrap: wrap;
    }
    .committee-card .cc-actions .btn { font-size: 11.5px; font-weight: 600; border-radius: 7px; padding: 5px 10px; }

    /* Progress bar for accomplishments */
    .acc-bar-wrap { height: 5px; background: #e2e8f0; border-radius: 3px; margin-top: 4px; overflow: hidden; }
    .acc-bar      { height: 100%; border-radius: 3px; }
</style>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Committee Management</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Track activities, accomplishments, and reports per committee.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13px;font-weight:600;padding:8px 16px;">
            <i class="bi bi-file-earmark-arrow-up-fill"></i> Upload Report
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2"
                style="border-radius:8px;font-size:13.5px;font-weight:600;padding:9px 18px;">
            <i class="bi bi-plus-lg"></i> Log Activity
        </button>
    </div>
</div>

{{-- ── SUMMARY STAT ROW ── --}}
<div class="section-heading">This Quarter at a Glance</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#eff6ff;color:#1a56db;"><i class="bi bi-people-fill"></i></div>
            <div><div class="stat-value">8</div><div class="stat-label">Active Committees</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-calendar-event-fill"></i></div>
            <div><div class="stat-value">47</div><div class="stat-label">Activities Logged</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div><div class="stat-value">23</div><div class="stat-label">Reports Uploaded</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fff7ed;color:#ea580c;"><i class="bi bi-images"></i></div>
            <div><div class="stat-value">124</div><div class="stat-label">Photos / Videos</div></div>
        </div>
    </div>
</div>

{{-- ── COMMITTEE CARDS ── --}}
<div class="section-heading">Committees</div>
<div class="row g-3">
    @php
    $committees = [
        [
            'name'    => 'Peace & Order',
            'chair'   => 'HON. Maria L. Santos',
            'icon'    => 'bi-shield-fill-check',
            'color'   => '#1a56db',
            'bg'      => '#eff6ff',
            'acts'    => 8,
            'accPct'  => 75,
            'reports' => 3,
            'media'   => 18,
        ],
        [
            'name'    => 'Health',
            'chair'   => 'HON. Juan P. dela Cruz',
            'icon'    => 'bi-heart-pulse-fill',
            'color'   => '#dc2626',
            'bg'      => '#fef2f2',
            'acts'    => 12,
            'accPct'  => 90,
            'reports' => 5,
            'media'   => 34,
        ],
        [
            'name'    => 'Education',
            'chair'   => 'HON. Ana B. Reyes',
            'icon'    => 'bi-book-fill',
            'color'   => '#7c3aed',
            'bg'      => '#f5f3ff',
            'acts'    => 6,
            'accPct'  => 60,
            'reports' => 2,
            'media'   => 14,
        ],
        [
            'name'    => 'Infrastructure',
            'chair'   => 'HON. Rosa T. Aquino',
            'icon'    => 'bi-building-fill',
            'color'   => '#d97706',
            'bg'      => '#fffbeb',
            'acts'    => 5,
            'accPct'  => 50,
            'reports' => 2,
            'media'   => 10,
        ],
        [
            'name'    => 'Environment',
            'chair'   => 'HON. Ernesto C. Lim',
            'icon'    => 'bi-tree-fill',
            'color'   => '#16a34a',
            'bg'      => '#f0fdf4',
            'acts'    => 7,
            'accPct'  => 70,
            'reports' => 3,
            'media'   => 20,
        ],
        [
            'name'    => 'Livelihood',
            'chair'   => 'HON. Carlo R. Mendoza',
            'icon'    => 'bi-briefcase-fill',
            'color'   => '#0d9488',
            'bg'      => '#f0fdfa',
            'acts'    => 4,
            'accPct'  => 45,
            'reports' => 3,
            'media'   => 8,
        ],
        [
            'name'    => 'Transport & Communication',
            'chair'   => 'HON. Luz A. Villanueva',
            'icon'    => 'bi-bus-front-fill',
            'color'   => '#0369a1',
            'bg'      => '#e0f2fe',
            'acts'    => 3,
            'accPct'  => 38,
            'reports' => 2,
            'media'   => 9,
        ],
        [
            'name'    => 'Disaster Risk Reduction & Mgmt (BDRRM)',
            'chair'   => 'HON. Roberto M. Cruz',
            'icon'    => 'bi-exclamation-triangle-fill',
            'color'   => '#ea580c',
            'bg'      => '#fff7ed',
            'acts'    => 2,
            'accPct'  => 55,
            'reports' => 3,
            'media'   => 11,
        ],
    ];
    @endphp

    @foreach($committees as $c)
    <div class="col-md-6 col-xl-4">
        <div class="committee-card">

            {{-- Header --}}
            <div class="cc-header" style="border-top: 3px solid {{ $c['color'] }};">
                <div class="cc-icon" style="background:{{ $c['bg'] }};color:{{ $c['color'] }};">
                    <i class="bi {{ $c['icon'] }}"></i>
                </div>
                <div>
                    <div class="cc-name">{{ $c['name'] }}</div>
                    <div class="cc-chair"><i class="bi bi-person-fill me-1"></i>{{ $c['chair'] }}</div>
                </div>
            </div>

            {{-- Accomplishments bar --}}
            <div class="px-4 pb-3">
                <div class="d-flex justify-content-between" style="font-size:12px;font-weight:600;color:#0f172a;">
                    <span>Accomplishments</span>
                    <span style="color:{{ $c['color'] }};">{{ $c['accPct'] }}%</span>
                </div>
                <div class="acc-bar-wrap">
                    <div class="acc-bar" style="width:{{ $c['accPct'] }}%;background:{{ $c['color'] }};"></div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="cc-stats">
                <div class="cc-stat">
                    <div class="cc-stat-val">{{ $c['acts'] }}</div>
                    <div class="cc-stat-lbl">Activities</div>
                </div>
                <div class="cc-stat">
                    <div class="cc-stat-val">{{ $c['reports'] }}</div>
                    <div class="cc-stat-lbl">Reports</div>
                </div>
                <div class="cc-stat">
                    <div class="cc-stat-val">{{ $c['media'] }}</div>
                    <div class="cc-stat-lbl">Media</div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="cc-actions">
                <button class="btn btn-light d-flex align-items-center gap-1">
                    <i class="bi bi-camera-fill"></i> Add Photo/Video
                </button>
                <button class="btn btn-light d-flex align-items-center gap-1">
                    <i class="bi bi-file-earmark-arrow-up"></i> Upload Report
                </button>
                <button class="btn btn-light d-flex align-items-center gap-1">
                    <i class="bi bi-person-lines-fill"></i> Attendance
                </button>
            </div>

        </div>
    </div>
    @endforeach

</div>

@endsection

@section('scripts')
{{-- No charts needed here; committee cards are self-contained. --}}
@endsection