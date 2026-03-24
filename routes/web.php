<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => Auth::check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::get('/dashboard', fn() => redirect()->route('residents.index'))
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // RESIDENTS ROUTE
    Route::get('/residents/data', [ResidentController::class, 'getResidents'])->name('residents.data');
    Route::resource('/residents', ResidentController::class);

    // USERS ROUTE
    Route::get('/users/data', [UserController::class, 'getUsers'])->name('users.data');
    Route::resource('/users', UserController::class);

    Route::get('/documents', fn() => view('documents.index'))
        ->middleware('can:documents.view')
        ->name('documents.index');

    Route::get('/blotter', fn() => view('blotters.index'))
        ->middleware('can:blotter.view')
        ->name('blotter.index');

    Route::get('/households', fn() => view('households_and_puroks.households'))
        ->middleware('can:households.view')
        ->name('households.index');

    Route::get('/business', fn() => view('businesses.index'))
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
