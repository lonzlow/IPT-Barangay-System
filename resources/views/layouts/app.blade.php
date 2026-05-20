<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Barangay Management System')</title>

    <!-- Favicon / Webpage Icon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo/Barangay New Era Logo.jpg') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bs-font-sans-serif: 'Plus Jakarta Sans', sans-serif;
            --primary:    #1a56db;
            --primary-lt: #eff6ff;
            --sidebar-bg: #0f172a;
            --sidebar-w:  260px;
            --topbar-h:   64px;
            --border:     #e2e8f0;
            --surface:    #f8fafc;
            --muted:      #64748b;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--surface); color: #1e293b; min-height: 100vh; }

        /* ── SIDEBAR ── */
        #sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            z-index: 1040; transition: transform .3s ease;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 20px 24px 16px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex; align-items: center; gap: 12px;
            text-decoration: none; flex-shrink: 0;
        }
        .sidebar-brand .brand-icon {
            width: 38px; height: 38px; background: var(--primary); border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff; flex-shrink: 0;
        }
        .sidebar-brand .brand-text { font-size: 13px; font-weight: 700; color: #f1f5f9; line-height: 1.3; }
        .sidebar-brand .brand-sub  { font-size: 10px; color: #94a3b8; font-weight: 500; letter-spacing: .5px; text-transform: uppercase; }

        .sidebar-nav { flex: 1; padding: 12px; }
        .sidebar-nav .nav-label {
            font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
            color: #475569; padding: 12px 12px 6px;
        }
        .sidebar-nav .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: #94a3b8; font-size: 13.5px; font-weight: 500;
            text-decoration: none; transition: all .18s; margin-bottom: 2px;
        }
        .sidebar-nav .nav-link i { font-size: 16px; flex-shrink: 0; }
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,.06); color: #e2e8f0; }
        .sidebar-nav .nav-link.active { background: rgba(26,86,219,.25); color: #93c5fd; }
        .sidebar-nav .nav-link.active i { color: #60a5fa; }
        .sidebar-nav .nav-link.disabled-link { opacity: .35; pointer-events: none; cursor: default; }

        .sidebar-footer { padding: 14px 16px; border-top: 1px solid rgba(255,255,255,.07); flex-shrink: 0; }
        .sidebar-footer .logout-btn {
            width: 100%; margin-top: 10px; border: 0; border-radius: 8px;
            background: rgba(255,255,255,.06); color: #cbd5e1;
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; font-size: 13.5px; font-weight: 500;
            transition: all .18s;
        }
        .sidebar-footer .logout-btn:hover {
            background: rgba(255,255,255,.1); color: #fff;
        }

        /* ── TOPBAR ── */
        #topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: var(--topbar-h); background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; padding: 0 28px; gap: 16px; z-index: 1030;
        }
        .topbar-title     { font-size: 16px; font-weight: 700; color: #0f172a; line-height: 1.2; }
        .topbar-breadcrumb{ font-size: 11.5px; color: var(--muted); display: flex; align-items: center; gap: 5px; }
        .topbar-breadcrumb .bc-sep     { color: #cbd5e1; }
        .topbar-breadcrumb .bc-current { color: #1e293b; font-weight: 600; }
        .topbar-actions { display: flex; align-items: center; gap: 10px; }
        .btn-icon {
            width: 36px; height: 36px; border-radius: 8px;
            border: 1px solid var(--border); background: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--muted); font-size: 16px; cursor: pointer; transition: all .15s;
        }
        .btn-icon:hover { background: var(--surface); color: #1e293b; }
        .avatar {
            width: 34px; height: 34px; border-radius: 50%; background: var(--primary);
            color: #fff; font-size: 13px; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
        }

        /* ── MAIN ── */
        #main-content { margin-left: var(--sidebar-w); padding-top: var(--topbar-h); min-height: 100vh; }
        .page-body { padding: 28px; }

        /* ── SHARED COMPONENTS ── */
        .stat-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 20px 22px; transition: box-shadow .2s; }
        .stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); }
        .stat-card .stat-icon { width: 44px; height: 44px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .stat-card .stat-value { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1; font-family: 'DM Mono', monospace; }
        .stat-card .stat-label { font-size: 12.5px; color: var(--muted); font-weight: 500; margin-top: 4px; }
        .stat-card .stat-badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }

        .chart-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 22px; }
        .chart-card .card-heading { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .chart-card .card-sub     { font-size: 12px; color: var(--muted); }

        .table-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
        .table-card .table-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .table-card .table-header .heading { font-size: 14px; font-weight: 700; color: #0f172a; flex: 1; }
        .table-card table { margin: 0; }
        .table-card table thead th { background: var(--surface); font-size: 11.5px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--border); padding: 10px 16px; }
        .table-card table tbody td { font-size: 13.5px; padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .table-card table tbody tr:last-child td { border-bottom: none; }
        .table-card table tbody tr:hover td { background: #f8fafc; }

        .status-badge { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .section-heading { font-size: 13px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: #94a3b8; margin-bottom: 14px; }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #topbar { left: 0; }
            #main-content { margin-left: 0; }
            .page-body { padding: 18px; }
        }
    </style>

    @yield('styles')
</head>
<body>

{{-- ── SIDEBAR ── --}}
<nav id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="flex-shrink-0">
                    <img src="{{ asset('images/logo/Barangay New Era Logo.jpg') }}" alt="Barangay Logo"
                        class="w-14 h-14 object-cover rounded-full border-2 border-white shadow-md">
                </div>
        <div>
            <div class="brand-text">Barangay New Era</div>
            <div class="brand-sub">Management System</div>
        </div>
    </a>

    <div class="sidebar-nav">

        <div class="nav-label">Main</div>
        <a href="{{ route('dashboard') }}"
           class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        @canany([
            'residents.view',
            'documents.view',
            'blotter.view',
            'households.view',
            'business.view',
            'officials.view',
            'committees.view'
        ])
            <div class="nav-label mt-2">Modules</div>

            @can('residents.view')
                <a href="{{ route('residents.index') }}"
                   class="nav-link {{ Request::routeIs('residents.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Residents
                </a>
            @endcan

            @can('documents.view')
                <a href="{{ route('documents.index') }}"
                   class="nav-link {{ Request::routeIs('documents.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text-fill"></i> Document Issuance
                </a>
            @endcan

            @can('blotter.view')
                <a href="{{ route('blotters.index') }}"
                   class="nav-link {{ Request::routeIs('blotters.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Blotter
                </a>
            @endcan

            @can('households.view')
                <a href="{{ route('households.index') }}"
                   class="nav-link {{ Request::routeIs('households.*') ? 'active' : '' }}">
                    <i class="bi bi-house-heart-fill"></i> Households & Purok
                </a>
            @endcan

            @can('business.view')
                <a href="{{ route('business.index') }}"
                   class="nav-link {{ Request::routeIs('business.*') ? 'active' : '' }}">
                    <i class="bi bi-shop-window"></i> Business Permits
                </a>
            @endcan

            @can('officials.view')
                <a href="{{ route('officials.index') }}"
                   class="nav-link {{ Request::routeIs('officials.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-fill-check"></i> Officials & Staff
                </a>
            @endcan

            @can('committees.view')
                <a href="{{ route('committees.index') }}"
                   class="nav-link {{ Request::routeIs('committees.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Committees
                </a>
            @endcan
        @endcanany

        @canany(['reports.view', 'users.view'])
            <div class="nav-label mt-2">System</div>

            @can('reports.view')
                <a href="{{ route('reports.index') }}"
                   class="nav-link {{ Request::routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill"></i> Reports & Analytics
                </a>
            @endcan

            @can('users.view')
                <a href="{{ route('users.index') }}"
                   class="nav-link {{ Request::routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lock"></i> User Management
                </a>
            @endcan
        @endcanany

    </div>

    <div class="sidebar-footer">
        @php
    // Kukuha tayo direkta sa Auth session ng Laravel
    $currentUser = auth()->user();

    // 1. TRAVERSE SA TATLONG TABLES PARA MAKUHA ANG PANGALAN: User -> Official -> Resident
    $residentProfile = $currentUser?->official?->resident;

    if ($residentProfile) {
        $firstName = $residentProfile->first_name;
        $lastName = $residentProfile->last_name;
        $fullName = trim($firstName . ' ' . $lastName);
    } else {
        // Fallback placeholder kapag walang nakatali na Resident record (gaya ng default admin account mo)
        $fullName = $currentUser?->name ?: 'System Administrator';
    }

    // 2. GENERATE INITIALS (Juan Dela Cruz -> JD o System Administrator -> SA)
    $words = explode(' ', preg_replace('/\s+/', ' ', trim($fullName)));
    $initials = '';
    
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
    } elseif (count($words) == 1 && !empty($words[0])) {
        $initials = strtoupper(substr($words[0], 0, 2));
    } else {
        $initials = 'SA'; // Default System Admin Initial
    }

    // 3. TRAVERSE PARA MAKUHA ANG ROLE NAME: User -> Official -> Role
    $roleName = $currentUser?->official?->role?->role_name ?? 'Authenticated User';
@endphp

<div class="d-flex align-items-center gap-2 px-3 py-2">
    <div class="avatar flex items-center justify-center bg-blue-600 text-white font-bold rounded-full uppercase shrink-0" 
         style="width: 40px; height: 40px; font-size: 14px; letter-spacing: 0.05em; min-width: 40px;">
        {{ $initials }}
    </div>
    
    <div class="min-w-0 flex-1">
        <div class="truncate" style="font-size:13px; font-weight:600; color:#cbd5e1; line-height: 1.2;">
            {{ $fullName }}
        </div>
        <div class="truncate" style="font-size:11px; color:#475569; margin-top: 3px;">
            {{ $roleName }}
        </div>
    </div>
</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</nav>

{{-- ── TOPBAR ── --}}
@php
    $currentUser = auth()->user();
    $residentProfile = $currentUser?->official?->resident;

    if ($residentProfile) {
        $firstName = $residentProfile->first_name;
        $lastName = $residentProfile->last_name;
        $fullName = trim($firstName . ' ' . $lastName);
    } else {
        $fullName = $currentUser?->name ?: 'System Administrator';
    }

    $words = explode(' ', preg_replace('/\s+/', ' ', trim($fullName)));
    $initials = '';
    
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
    } elseif (count($words) == 1 && !empty($words[0])) {
        $initials = strtoupper(substr($words[0], 0, 2));
    } else {
        $initials = 'SA';
    }
@endphp

<header id="topbar">
    <button class="btn-icon d-lg-none" id="sidebarToggle" style="border:none;">
        <i class="bi bi-list" style="font-size:20px;"></i>
    </button>
    <div>
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-breadcrumb">
            <span>Barangay New Era</span>
            <span class="bc-sep">›</span>
            <span class="bc-current">@yield('page-title', 'Dashboard')</span>
        </div>
    </div>
    <div class="topbar-actions ms-auto">
        <button class="btn-icon" title="Notifications"><i class="bi bi-bell"></i></button>
        <button class="btn-icon" title="Help"><i class="bi bi-question-circle"></i></button>
        <div class="avatar flex items-center justify-center bg-blue-600 text-white font-bold rounded-full uppercase" 
             style="width: 36px; height: 36px; font-size: 13px; letter-spacing: 0.05em;"
             title="{{ $fullName }}">
            {{ $initials }}
        </div>
    </div>
</header>

{{-- ── PAGE CONTENT ── --}}
<main id="main-content">
    <div class="page-body">
        @yield('content')
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    @yield('scripts') 
</body>
@yield('scripts')
</body>
</html>
