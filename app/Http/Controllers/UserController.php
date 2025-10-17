<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $grouped = [
            'all' => $users,
            'developers' => $users->filter(fn($u) => $u->hasRole('developer'))->values(),
            'mentors' => $users->filter(fn($u) => $u->hasRole('mentor'))->values(),
            'admins' => $users->filter(fn($u) => $u->hasRole('admin'))->values(),
        ];

        return response()->json($grouped, Response::HTTP_OK);
    }
}
