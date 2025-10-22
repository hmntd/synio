<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index(): JsonResponse
    {
        $tenant = auth()->user()->tenant;

        return response()->json([
            'success' => true,
            'invitations' => $tenant->invitations()->get(),
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = auth()->user()->tenant;

        $invitation = Invitation::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->user()->id,
            'role_id' => $request->role_id,
            'email' => $request->email,
            'token' => (string) Str::uuid(),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'actor_id' => auth()->user()->id,
            'action' => 'invited the user with email ' . $request->email,
        ]);

        return response()->json([
            'success' => true,
            'invitation' => $invitation,
        ], Response::HTTP_CREATED);
    }

    public function destroy(Invitation $invitation): JsonResponse
    {
        if ($invitation->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        ActivityLog::create([
            'tenant_id' => $invitation->tenant_id ?? auth()->user()->tenant_id,
            'actor_id' => auth()->user()->id,
            'action' => 'delete the invitation of the user with email ' . $invitation->email,
        ]);

        $invitation->delete();

        return response()->json([
            'success' => true,
        ], Response::HTTP_NO_CONTENT);
    }
}
