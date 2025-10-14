<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\IntegrationController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'ensure.tenant'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('settings/integrations')->group(function () {
        Route::get('/', [IntegrationController::class, 'edit'])->name('integrations.edit');
        Route::patch('/', [IntegrationController::class, 'update'])->name('integrations.update');

        // Redmine
        Route::post('/test-redmine', [IntegrationController::class, 'testRedmineKey'])
            ->middleware('throttle:6,1')
            ->name('integrations.test-redmine');
        Route::delete('/clear-redmine-key', [IntegrationController::class, 'clearRedmineKey'])
            ->name('integrations.clear-redmine-key');

        // Slack
        Route::post('/test-slack', [IntegrationController::class, 'testSlackKey'])->name('integrations.test-slack');
        Route::delete('/clear-slack-key', [IntegrationController::class, 'clearSlackKey'])->name('integrations.clear-slack-key');

        // Telegram
        Route::delete('/clear-telegram-key', [IntegrationController::class, 'clearTelegramKey'])->name('integrations.clear-telegram-key');
    });

    Route::get('settings/notifications', fn() => Inertia::render('settings/Notifications'))->name('notifications.edit');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});
