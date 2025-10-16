<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdateNotificationRequest;
use App\Services\SlackService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private SlackService $slackService,
        private TelegramService $telegramService
    ) {}

    public function update(UpdateNotificationRequest $request): RedirectResponse
    {
        $request->user()->notificationSettings()->update([
            'frequency' => $request->input('frequency'),
            'send_at' => $request->input('time'),
            'enabled' => $request->input('enabled'),
            'day_of_week' => $request->input('day_of_week'),
        ]);

        return back()->with('status', 'notifications-updated');
    }

    public function sendSlackNotification(Request $request): JsonResponse
    {
        return response()->json($this->slackService->sendMessage($request->user(), $request->input('message')));
    }

    public function sendTelegramNotification(Request $request): JsonResponse
    {
        return response()->json($this->telegramService->sendMessage($request->user(), $request->input('message')));
    }
}
