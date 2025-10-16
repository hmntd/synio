<?php

use App\Jobs\SyncRedmineProjectsJob;
use App\Jobs\SyncRedmineTimeEntriesJob;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $users = User::whereNotNull('redmine_api_key')->get();

    foreach ($users as $user) {
        SyncRedmineProjectsJob::dispatch($user)->chain([
            new SyncRedmineTimeEntriesJob($user),
        ]);
    }
})->dailyAt('00:00')->name('sync-redmine-data')->withoutOverlapping();

Schedule::command('app:send-notifications')->everyMinute()->name('send-notifications');