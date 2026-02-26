<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('residents.index'));

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
