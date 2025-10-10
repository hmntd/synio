<?php

use App\Http\Controllers\TimeEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/create-token', function (Request $request) {
    auth()->user()->tokens()->delete();
    $token = auth()->user()->createToken('api_token')->plainTextToken;
    return response()->json([
        'token' => $token,
    ], 200);
})->middleware(['web', 'auth']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/time-entries', [TimeEntryController::class, 'store'])->name('time-entries.store');
    Route::put('/time-entries/{timeEntryId}', [TimeEntryController::class, 'update'])->name('time-entries.update');
    Route::delete('/time-entries/{timeEntryId}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');

    Route::get('/projects/{projectId}/time-entries', [TimeEntryController::class, 'get']); // project time entries
});
