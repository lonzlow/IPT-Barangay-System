<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::get('/dashboard', fn() => redirect()->route('residents.index'))
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/residents', fn() => view('residents'))
        ->middleware('can:residents.view')
        ->name('residents.index');

    Route::get('/documents', fn() => view('documents'))
        ->middleware('can:documents.view')
        ->name('documents.index');

    Route::get('/blotter', fn() => view('blotter'))
        ->middleware('can:blotter.view')
        ->name('blotter.index');

    Route::get('/households', fn() => view('households'))
        ->middleware('can:households.view')
        ->name('households.index');

    Route::get('/business', fn() => view('business'))
        ->middleware('can:business.view')
        ->name('business.index');

    Route::get('/officials', fn() => view('officials'))
        ->middleware('can:officials.view')
        ->name('officials.index');

    Route::get('/committees', fn() => view('committees'))
        ->middleware('can:committees.view')
        ->name('committees.index');

    Route::get('/reports', fn() => view('reports'))
        ->middleware('can:reports.view')
        ->name('reports.index');

    Route::get('/users', fn() => view('users'))
        ->middleware('can:users.view')
        ->name('users.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
