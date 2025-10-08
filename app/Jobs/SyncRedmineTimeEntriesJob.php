<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\RedmineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncRedmineTimeEntriesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RedmineService $redmineService): void
    {
        $user = $this->user;

        if (! $user->redmine_api_key) {
            Log::info("Skipping time entry sync for user {$user->id}: no API key");
            return;
        }

        $projects = $user->projects()->get();
    }
}
