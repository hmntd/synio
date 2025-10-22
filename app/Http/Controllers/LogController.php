<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->can('view-logs')) {
            return response()->json([
                'message' => 'Forbidden',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $users = User::with([
            'roles',
            'activityLogs' => function ($query) {
                $query->orderByDesc('created_at');
            }
        ])->get();

        $users = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $u->roles->pluck('name'),
                'activity_logs' => $u->activityLogs,
            ];
        });

        return response()->json([
            'users' => $users,
        ], Response::HTTP_OK);
    }
}
