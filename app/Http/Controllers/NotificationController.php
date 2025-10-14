<?php

namespace App\Http\Controllers;

use App\Services\SlackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private SlackService $slackService,
    ) {}

    public function sendSlackNotification(Request $request): JsonResponse
    {
        return response()->json($this->slackService->sendMessage($request->user(), $request->input('message')));
    }
}
