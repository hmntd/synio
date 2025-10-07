<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::domain('{tenant}.' . config('app.domain'))
    ->middleware(['ensure.tenant'])
    ->group(function () {
        Route::get('/', fn() => Inertia::render('Welcome'));

        Route::get('/dashboard', fn() => Inertia::render('Dashboard'));
    });
