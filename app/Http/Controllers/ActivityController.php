<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ActivityController extends Controller
{
    public function index(): HttpFoundationResponse
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        $activities = $tenant->redmineActivities()->get();

        return response()->json([
            'activities' => $activities
        ], Response::HTTP_OK);
    }
}
