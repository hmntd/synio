<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::domain(config('app.domain'))->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified', 'ensure.tenant'])->name('dashboard');
});

require __DIR__ . '/tenant.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
