<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Barangay Management System – Web Routes
|--------------------------------------------------------------------------
| All 9 modules are wired here. The sidebar in layouts/app.blade.php uses
| route() helpers and Request::routeIs() for active-link highlighting.
|
| File placement in resources/views/:
|   layouts/app.blade.php
|   residents.blade.php
|   documents.blade.php
|   blotter.blade.php
|   households.blade.php
|   business.blade.php
|   officials.blade.php
|   committees.blade.php
|   reports.blade.php
|   users.blade.php
*/

// ── Root redirect ─────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('residents.index'));

Route::get('/dashboard', fn() => redirect()->route('residents.index'))
    ->name('dashboard');

// ── 1. Resident Management ────────────────────────────────────────────
Route::get('/residents', fn() => view('residents'))
    ->name('residents.index');

// ── 2. Document Issuance ──────────────────────────────────────────────
Route::get('/documents', fn() => view('documents'))
    ->name('documents.index');

// ── 3. Blotter Management ─────────────────────────────────────────────
Route::get('/blotter', fn() => view('blotter'))
    ->name('blotter.index');

// ── 4. Household & Purok Management ──────────────────────────────────
Route::get('/households', fn() => view('households'))
    ->name('households.index');

// ── 5. Business Permit Management ────────────────────────────────────
Route::get('/business', fn() => view('business'))
    ->name('business.index');

// ── 6. Officials & Staff Management ──────────────────────────────────
Route::get('/officials', fn() => view('officials'))
    ->name('officials.index');

// ── 7. Committee Management ───────────────────────────────────────────
Route::get('/committees', fn() => view('committees'))
    ->name('committees.index');

// ── 8. Reports & Analytics ────────────────────────────────────────────
Route::get('/reports', fn() => view('reports'))
    ->name('reports.index');

// ── 9. User Management & Access Control ──────────────────────────────
Route::get('/users', fn() => view('users'))
    ->name('users.index');

// ── Settings (placeholder) ────────────────────────────────────────────
// Route::get('/settings', fn() => view('settings'))->name('settings.index');
