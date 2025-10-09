<?php

use App\Http\Controllers\Data\ProjectsController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::domain('{tenant}.' . config('app.domain'))
    ->middleware(['ensure.tenant'])
    ->group(function () {
        Route::get('/dashboard', fn() => Inertia::render('Dashboard'));

        Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');

        Route::get('/time-entries', fn() => Inertia::render('TimeEntries'));

        // Route::get('/projects/{projectId}', function ($tenant, $projectId) {
        //     $project = \App\Models\Project::where('id', $projectId)
        //         ->firstOrFail();
        //     if (!$project) {
        //         return redirect(route('projects.index'));
        //     }

        //     return Inertia::render('Project', [
        //         'project' => $project,
        //         'time_entries' => $project->timeEntries()->get(),
        //     ]);
        // })->name('projects.show');
        Route::get('/projects/{projectId}', [TimeEntryController::class, 'indexProject'])->name('projects.show');
    });
