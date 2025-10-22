<?php

namespace App\Http\Controllers;

use App\Models\Mentorship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MentorshipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->can('send-mentorship-invite')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this page.',
            ], Response::HTTP_FORBIDDEN);
        }

        $mentorships = Mentorship::where('mentor_id', $user->id)
            ->with([
                'mentee.projects.timeEntries.activity',
            ])
            ->get()
            ->groupBy('status');

        $result = $mentorships->map(
            fn($group) =>
            $group->pluck('mentee')
        );

        return response()->json([
            'success' => true,
            'approved' => $result->get('approved', collect([])),
            'pending' => $result->get('pending', collect([])),
            'declined' => $result->get('declined', collect([])),
        ], Response::HTTP_OK);
    }
}
