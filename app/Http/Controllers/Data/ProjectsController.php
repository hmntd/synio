<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Tenant;
use App\Services\RedmineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProjectsController extends Controller
{
    public function __construct(
        private RedmineService $redmineService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $projects = $user->projects()->get();
        $message = !$user->redmine_api_key ? "User hasn't provided Redmine API key" : '';

        if ($projects->isEmpty() && $user->redmine_api_key) {
            try {
                $remoteProjects = $this->redmineService->fetchProjects($user);
                foreach ($remoteProjects as $remote) {
                    Project::updateOrCreate([
                        'tenant_id' => $user->tenant_id,
                        'redmine_id' => $remote['redmine_id'],
                    ], [
                        'user_id' => $user->id,
                        'name' => $remote['name'],
                        'identifier' => $remote['identifier'],
                        'description' => $remote['description'] ?? '',
                        'homepage' => $remote['homepage'] ?? '',
                        'is_public' => $remote['is_public'],
                    ]);
                }

                $projects = $user->projects()->get();
            } catch (\Exception $e) {
                Log::error('Failed to fetch Redmine projects', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'projects' => $projects,
            'message' => $message,
        ], Response::HTTP_OK);
    }
}
