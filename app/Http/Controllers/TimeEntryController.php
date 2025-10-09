<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Services\RedmineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TimeEntryController extends Controller
{
    public function __construct(
        private RedmineService $redmineService
    ) {}

    public function indexProject(Request $request): InertiaResponse
    {
        $project = Project::where('id', $request->projectId)->first();
        if (!$project) {
            return Inertia::to(Route('projects.index'));
        }

        $user = $request->user();
        $timeEntries = $user->timeEntries()->get();
        if ($timeEntries->empty() && $user->redmine_api_key) {
            try {
                $remoteEntries = $this->redmineService->fetchTimeEntries($user);
                foreach ($remoteEntries as $remote) {
                    TimeEntry::updateOrCreate([
                        'tenant_id' => $user->tenant_id,
                        'redmine_id' => $remote['redmine_id'],
                    ], [
                        'user_id' => $user->id,
                        // 'issue_id' => $remote['issue'] ? $remote['issue']['id'] : null,
                        'project_id' => $project->id,
                        'spent_on' => $remote['spent_on'],
                        'hours' => $remote['hours'],
                        // 'activity_id' => $remote['activity_id'],
                        'activity' => $remote['activity_name'],
                        'comments' => $remote['comments'],
                    ]);
                }

                $timeEntries = $user->timeEntries()->with('user')->get();
            } catch (\Exception $e) {
                Log::error('Failed to fetch Redmine projects', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return Inertia::render('Project', [
            'project' => $project,
            'time_entries' => $timeEntries,
        ]);
    }
}
