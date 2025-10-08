<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Services\RedmineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncRedmineProjectsJob implements ShouldQueue
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
            Log::info('Skipping user {$user->id}: no Redmine API key.');
            return;
        }


        try {
            $remoteProjects = $redmineService->fetchProjects($user);

            foreach ($remoteProjects as $remote) {
                Project::updateOrCreate(
                    [
                        'tenant_id' => $user->tenant_id,
                        'redmine_id' => $remote['redmine_id'],
                    ],
                    [
                        'user_id' => $user->id,
                        'name' => $remote['name'],
                        'identifier' => $remote['identifier'],
                        'description' => $remote['description'] ?? '',
                        'homepage' => $remote['homepage'] ?? '',
                        'is_public' => $remote['is_public'],
                    ]
                );
            }

            Log::info('Redmine projects synced successfully for user {$user->id}');
        } catch (\Throwable $e) {
            Log::error('Failed to sync Redmine projects for user ' . $user->id . ':' . ' ' . $e->getMessage() . '');
        }
    }
}
