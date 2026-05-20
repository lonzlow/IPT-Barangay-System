<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CertificateTemplateController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\PurokController;
use App\Http\Controllers\BlotterController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CommitteeRecordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => Auth::check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::get('/dashboard', fn() => redirect()->route('residents.index'))
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // BLOTTERS ROUTE
    Route::get('/blotters/data', [BlotterController::class, 'data'])->name('blotters.data');
    Route::get('/blotters/export', [BlotterController::class, 'export'])->name('blotters.export');
    Route::get('/blotters/residents/search', [BlotterController::class, 'residentsSearch'])->name('blotters.residents.search');
    Route::post('/blotters/{blotter}/evidence', [BlotterController::class, 'uploadEvidence'])->name('blotters.evidence');
    Route::resource('blotters', BlotterController::class);

    // OFFICIALS ROUTE
    Route::resource('officials', OfficialController::class);

    // COMMITTEES ROUTE
    Route::middleware('can:committees.view')->group(function () {
        Route::post('/committees/{committee}/records', [CommitteeRecordController::class, 'store'])
            ->name('committees.records.store');
        Route::put('/committee-records/{record}', [CommitteeRecordController::class, 'update'])
            ->name('committee-records.update');
        Route::delete('/committee-records/{record}', [CommitteeRecordController::class, 'destroy'])
            ->name('committee-records.destroy');
        Route::resource('committees', CommitteeController::class);
    });

    // REPORTS ROUTE
    Route::middleware('can:reports.view')->group(function () {
        Route::resource('reports', ReportController::class)->parameters(['reports' => 'record']);
    });

    // BUSINESS ROUTE
    Route::middleware('can:business.view')->group(function () {
        Route::get('/businesses/data', [BusinessController::class, 'data'])->name('businesses.data');
        Route::get('/businesses/{business}/permits/history', [BusinessController::class, 'permitHistory'])->name('businesses.permits.history');
        Route::post('/businesses/{business}/permits', [BusinessController::class, 'issuePermit'])->name('businesses.permits.issue');
        Route::post('/businesses/{business}/permits/{permit}/renew', [BusinessController::class, 'renewPermit'])->name('businesses.permits.renew');
        Route::resource('businesses', BusinessController::class);
    });

    // RESIDENTS ROUTE
    Route::get('/residents/data', [ResidentController::class, 'getResidents'])->name('residents.data');
    Route::get('/residents/demographics', [ResidentController::class, 'demographics'])->name('residents.demographics');
    Route::get('/residents/export-pdf', [ResidentController::class, 'exportPDF'])->name('residents.exportPDF');
    Route::resource('/residents', ResidentController::class);

    // DOCUMENTS ROUTE
    Route::middleware('can:documents.view')->group(function () {
        Route::get('/documents/data', [DocumentController::class, 'data'])->name('documents.data');
        Route::get('/documents/audit-logs', [DocumentController::class, 'auditLogs'])->name('documents.auditLogs');
        Route::post('/documents/preview', [DocumentController::class, 'preview'])->name('documents.preview');
        Route::get('/documents/residents/{resident}/businesses', [DocumentController::class, 'residentBusinesses'])->name('documents.residentBusinesses');
        Route::get('/documents/residents/{resident}/documents', [DocumentController::class, 'getResidentDocuments'])->name('documents.getResidentDocuments');
        Route::get('/documents/{document}/download-pdf', [DocumentController::class, 'downloadPdf'])->name('documents.downloadPdf');
        Route::get('/documents/{document}/export-pdf', [DocumentController::class, 'exportPdf'])->name('documents.exportPdf');
        Route::resource('/documents', DocumentController::class)->whereUuid('document');
    });

    // CERTIFICATE TEMPLATES ROUTE
    Route::middleware('can:documents.view')->group(function () {
        Route::resource('/certificate-templates', CertificateTemplateController::class);
    });

    // SIGNATURES (upload/delete) — admin only via signatures.manage gate
    Route::middleware('can:signatures.manage')->group(function () {
        Route::post('/signatures', [SignatureController::class, 'store'])->name('signatures.store');
        Route::delete('/signatures/{signature}', [SignatureController::class, 'destroy'])->name('signatures.destroy');
    });

    // USERS ROUTE (ADMIN ONLY)
    Route::middleware('can:users.view')->group(function () {
        Route::get('/users/data', [UserController::class, 'getUsers'])->name('users.data');
        Route::resource('/users', UserController::class);
    });

    // HOUSEHOLDS ROUTE
    Route::middleware('can:households.view')->group(function () {
        Route::get('/households/data', [HouseholdController::class, 'data'])->name('households.data');
        Route::get('/households/statistics', [HouseholdController::class, 'statistics'])->name('households.statistics');
        Route::resource('/households', HouseholdController::class);
    });

    // PUROKS ROUTE
    Route::middleware('can:households.view')->group(function () {
        Route::resource('/puroks', PurokController::class);
    });

    // PROFILE ROUTES
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
