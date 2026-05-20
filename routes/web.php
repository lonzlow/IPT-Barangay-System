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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BusinessController;
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
    Route::resource('blotters', BlotterController::class);

    // OFFICIALS ROUTE
    Route::resource('officials', OfficialController::class);

    // COMMITTEES ROUTE
    Route::resource('committees', CommitteeController::class);

    // REPORTS ROUTE
    Route::resource('reports', ReportController::class);

    // BUSINESS ROUTE
    Route::resource('business', BusinessController::class);

    // RESIDENTS ROUTE
    Route::get('/residents/data', [ResidentController::class, 'getResidents'])->name('residents.data');
    Route::get('/residents/demographics', [ResidentController::class, 'demographics'])->name('residents.demographics');
    Route::get('/residents/export-pdf', [ResidentController::class, 'exportPDF'])->name('residents.exportPDF');
    Route::patch('/residents/{id}/recover', [ResidentController::class, 'recover'])->name('residents.recover');
    Route::resource('/residents', ResidentController::class);

    // DOCUMENTS ROUTE
    Route::middleware('can:documents.view')->group(function () {
        Route::get('/documents/data', [DocumentController::class, 'data'])->name('documents.data');
        Route::get('/documents/{document}/download-pdf', [DocumentController::class, 'downloadPdf'])->name('documents.downloadPdf');
        Route::get('/documents/{document}/export-pdf', [DocumentController::class, 'exportPdf'])->name('documents.exportPdf');
        Route::get('/documents/{document}/resident-documents', [DocumentController::class, 'getResidentDocuments'])->name('documents.getResidentDocuments');
        Route::resource('/documents', DocumentController::class);
    });

    // CERTIFICATE TEMPLATES ROUTE
    Route::middleware('can:documents.view')->group(function () {
        Route::resource('/certificate-templates', CertificateTemplateController::class);
    });

    // USERS ROUTE (ADMIN ONLY)
    Route::middleware('can:users.view')->group(function () {
        Route::get('/users/data', [UserController::class, 'getUsers'])->name('users.data');
        Route::resource('/users', UserController::class);
    });

    // HOUSEHOLDS ROUTE
    Route::middleware('can:households.view')->group(function () {
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

require __DIR__ . '/auth.php';
