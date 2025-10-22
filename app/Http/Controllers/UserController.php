<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->can('view-users')) {
            return response()->json([
                'message' => 'Forbidden',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $users = User::with('roles')->get();
        $tenant = auth()->user()->tenant;

        $grouped = [
            'all' => $users,
            'developers' => $users->filter(fn($u) => $u->hasRole('developer'))->values(),
            'mentors' => $users->filter(fn($u) => $u->hasRole('mentor'))->values(),
            'admins' => $users->filter(fn($u) => $u->hasRole('admin'))->values(),
            'invitations' => $tenant->invitations()->with('role')->get(),
        ];

        return response()->json($grouped, Response::HTTP_OK);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $actionUser = $request->user();
        if (!$actionUser) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$actionUser->can('view-users')) {
            return response()->json([
                'message' => 'Forbidden',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $result = $user->update($request->all());
        $role = Role::find($request->role_id);
        $user->syncRoles($role->name);

        ActivityLog::create([
            'tenant_id' => $actionUser->tenant_id,
            'actor_id' => $actionUser->id,
            'target_user_id' => $user->id,
            'action' => 'updated user',
        ]);

        return response()->json([
            'success' => $result,
            'message' => 'User updated successfully',
        ], Response::HTTP_OK);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $actionUser = $request->user();
        if (!$actionUser) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$actionUser->can('view-users')) {
            return response()->json([
                'message' => 'Forbidden',
            ], Response::HTTP_UNAUTHORIZED);
        }

        ActivityLog::create([
            'tenant_id' => $actionUser->tenant_id,
            'actor_id' => $actionUser->id,
            'action' => 'deleted user ' . $user->email,
        ]);

        $result = $user->delete();

        return response()->json([
            'success' => $result,
            'message' => 'User deleted successfully',
        ], Response::HTTP_NO_CONTENT);
    }
}
