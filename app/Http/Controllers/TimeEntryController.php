<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RedmineActivity;
use App\Models\TimeEntry;
use App\Services\RedmineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

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

        return Inertia::render('Project', [
            'project' => $project,
            'activities' => RedmineActivity::all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'spent_on' => 'required|date',
            'hours' => 'required|numeric',
            'activity_id' => 'required',
            'comments' => 'nullable',
            'project_id' => 'required|exists:projects,id',
        ]);

        $date = Carbon::parse($request->spent_on)->format('Y-m-d');

        $response = $this->redmineService->createTimeEntry($request->user(), [
            'issue_id' => null,
            'project_id' => $request->project_id,
            'date' => $date,
            'hours' => $request->hours,
            'activity_id' => $request->activity_id,
            'comments' => $request->comments,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $timeEntry = TimeEntry::updateOrCreate([
                'tenant_id' => $request->user()->tenant_id,
                'redmine_id' => $data['time_entry']['id'],
            ], [
                'user_id' => $request->user()->id,
                'issue_id' => null,
                'project_id' => $request->project_id,
                'spent_on' => $date,
                'hours' => $request->hours,
                'activity_id' => $request->activity_id,
                'comments' => $request->comments ?? '',
            ]);
        }

        return response()->json([
            'time_entry' => $timeEntry,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'spent_on' => 'required|date',
            'hours' => 'required|numeric',
            'activity_id' => 'required',
            'comments' => 'nullable',
        ]);

        $timeEntry = TimeEntry::findOrFail($request->timeEntryId);

        $timeEntry->spent_on = Carbon::parse($request->spent_on)->format('Y-m-d');
        $response = $this->redmineService->updateTimeEntry($request->user(), $timeEntry->redmine_id, [
            'issue_id' => null,
            'project_id' => $timeEntry->project_id,
            'date' => $timeEntry->spent_on,
            'hours' => $request->hours,
            'activity_id' => $request->activity_id,
            'comments' => $request->comments,
        ]);

        if ($response->successful()) {
            $timeEntry->hours = $request->hours;
            $timeEntry->activity_id = $request->activity_id;
            $timeEntry->comments = $request->comments;
            $timeEntry->save();
        }

        return response()->json([
            'time_entry' => $timeEntry,
        ], Response::HTTP_OK);
    }

    public function destroy(Request $request): JsonResponse
    {
        $timeEntry = TimeEntry::findOrFail($request->timeEntryId);
        $project = $timeEntry->project;
        $direction = request()->input('sort', 'desc');
        $perPage = request()->integer('per_page', 25);

        $this->redmineService->deleteTimeEntry(auth()->user(), $timeEntry->redmine_id);
        $timeEntry->delete();

        return response()->json([
            'message' => 'Time entry deleted successfully',
        ], Response::HTTP_OK);
    }

    public function get(Request $request, Project $project): JsonResponse
    {
        $user  = $request->user();
        $direction = $request->input('sort', 'desc');
        $perPage = $request->integer('per_page', 25);
        $timeEntries = $user->timeEntries()
            ->with(['user', 'activity'])
            ->orderBy('spent_on', $direction)
            ->paginate($perPage);

        if ($timeEntries->isEmpty() && $user->redmine_api_key) {
            try {
                $remoteEntries = $this->redmineService->fetchTimeEntries($user);
                foreach ($remoteEntries as $remote) {
                    RedmineActivity::updateOrCreate([
                        'tenant_id' => $user->tenant_id,
                        'redmine_id' => $remote['activity_id'],
                    ], [
                        'name' => $remote['activity_name'],
                    ]);

                    TimeEntry::updateOrCreate([
                        'tenant_id' => $user->tenant_id,
                        'redmine_id' => $remote['redmine_id'],
                    ], [
                        'user_id' => $user->id,
                        // 'issue_id' => $remote['issue'] ? $remote['issue']['id'] : null,
                        'project_id' => $project->id,
                        'spent_on' => $remote['spent_on'],
                        'hours' => $remote['hours'],
                        'activity_id' => $remote['activity_id'],
                        'comments' => $remote['comments'],
                    ]);
                }

                $timeEntries = $user->timeEntries()
                    ->with(['user', 'activity'])
                    ->orderBy('spent_on', $direction)
                    ->paginate($perPage);
            } catch (\Exception $e) {
                Log::error('Failed to fetch Redmine projects', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'data' => $timeEntries->items(),
            'current_page' => $timeEntries->currentPage(),
            'last_page' => $timeEntries->lastPage(),
            'links' => $timeEntries->linkCollection(),
            'per_page' => $perPage,
            'direction' => $direction,
        ], Response::HTTP_OK);
    }
}
