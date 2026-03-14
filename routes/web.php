<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Default login page
Route::get('/', function () {
    return view('auth.login');
});

// Breeze login/register routes
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Protected Routes (auth + verified)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn() => redirect()->route('residents.index'))
        ->name('dashboard');

    Route::get('/residents', fn() => view('residents'))
        ->name('residents.index');
    Route::get('/documents', fn() => view('documents'))
        ->name('documents.index');
    Route::get('/blotter', fn() => view('blotter'))
        ->name('blotter.index');
    Route::get('/households', fn() => view('households'))
        ->name('households.index');
    Route::get('/business', fn() => view('business'))
        ->name('business.index');
    Route::get('/officials', fn() => view('officials'))
        ->name('officials.index');
    Route::get('/committees', fn() => view('committees'))
        ->name('committees.index');
    Route::get('/reports', fn() => view('reports'))
        ->name('reports.index');
    Route::get('/users', fn() => view('users'))
        ->name('users.index');

    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});