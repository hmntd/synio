<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Data\ProjectsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TimeEntryController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/create-token', function () {
    auth()->user()->tokens()->delete();
    $token = auth()->user()->createToken('api_token')->plainTextToken;
    return response()->json([
        'token' => $token,
    ], 200);
})->middleware(['web', 'auth']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/activities', [ActivityController::class, 'index'])->name('activity.index');

    Route::get('/time-entries', [TimeEntryController::class, 'index'])->name('time-entries.index'); // user time entries
    Route::post('/time-entries', [TimeEntryController::class, 'store'])->name('time-entries.store');
    Route::put('/time-entries/{timeEntryId}', [TimeEntryController::class, 'update'])->name('time-entries.update');
    Route::delete('/time-entries/{timeEntryId}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');

    Route::get('/projects', [ProjectsController::class, 'index']);
    Route::get('/projects/{projectId}/time-entries', [TimeEntryController::class, 'get']); // project time entries

    Route::get('/timezones', fn() => response()->json([
        'timezones' => DateTimeZone::listIdentifiers(),
    ]));
});

Route::post('/test-notification', function () {
    $user = User::find(1);
    dispatch(new \App\Jobs\SendUserNotification($user, $user->notificationSettings()->first()));
});
