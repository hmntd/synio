<?php

use App\Http\Controllers\TimeEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::domain('{tenant}.' . config('app.domain'))
    ->middleware(['ensure.tenant'])
    ->group(function () {
        Route::get('/dashboard', fn() => Inertia::render('Dashboard'));

        Route::get('/projects', fn() => Inertia::render('Projects'))->name('projects.index');

        Route::get('/time-entries', fn() => Inertia::render('TimeEntries'));

        Route::get('/projects/{projectId}', [TimeEntryController::class, 'indexProject'])->name('projects.show');

        Route::get('/activity-logs', function (Request $request) {
            if ($request->user()->can('view-logs')) {
                return Inertia::render('ActivityLogs');
            }

            return redirect()->route('dashboard');
        });

        Route::get('/users', function (Request $request) {
            if ($request->user()->can('view-users')) {
                return Inertia::render('admin/Users');
            }

            return redirect()->route('dashboard');
        });

        Route::get('/mentorships', function (Request $request) {
            if ($request->user()->can('send-mentorship-invite') || $request->user()->can('approve-mentorship-invite')) {
                return Inertia::render('admin/Mentorships');
            }

            return redirect()->route('dashboard');
        });
    });
