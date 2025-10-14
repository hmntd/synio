<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\IntegrationUpdateRequest;
use App\Models\User;
use App\Services\RedmineService;
use App\Services\SlackService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class IntegrationController extends Controller
{
    public function __construct(
        private RedmineService $redmineService,
        private SlackService $slackService,
        private TelegramService $telegramService,
    ) {}

    public function edit(Request $request): InertiaResponse
    {
        return Inertia::render('settings/Integrations', [
            'integrations' => [
                'redmine_base_url' => $request->user()->redmine_base_url,
                'redmine_api_key' => $request->user()->redmine_api_key,
                'slack_user_id' => $request->user()->slack_user_id,
                'telegram_chat_id' => $request->user()->telegram_chat_id,
            ],
            'routes' => [
                'update' => route('integrations.update'),
                'test_redmine' => route('integrations.test-redmine'),
                'clear_redmine_key' => route('integrations.clear-redmine-key'),
                'test_slack' => route('integrations.test-slack'),
                'clear_slack_key' => route('integrations.clear-slack-key'),
                'clear_telegram_key' => route('integrations.clear-telegram-key'),
            ],
        ]);
    }

    public function update(IntegrationUpdateRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        // Only update fields that are present in the request
        $updateData = [];

        if (isset($validated['redmine_base_url'])) {
            $updateData['redmine_base_url'] = $validated['redmine_base_url'] ?: null;
        }

        if (isset($validated['redmine_api_key'])) {
            $updateData['redmine_api_key'] = $validated['redmine_api_key'] ?: null;
        }

        if (isset($validated['slack_user_id'])) {
            $updateData['slack_user_id'] = $validated['slack_user_id'] ?: null;
        }

        if (isset($validated['telegram_user_id'])) {
            $updateData['telegram_user_id'] = $validated['telegram_user_id'] ?: null;
        }

        $user->update($updateData);

        return back()->with('status', 'integrations-updated');
    }

    public function testRedmineKey(Request $request): JsonResponse
    {
        $request->validate([
            'redmine_api_key' => ['required', 'string'],
            'redmine_base_url' => ['nullable', 'string', 'url'],
        ]);

        $user = $request->user();

        // Temporarily set the provided values for testing
        $originalApiKey = $user->redmine_api_key;
        $originalBaseUrl = $user->redmine_base_url;

        $user->redmine_api_key = $request->input('redmine_api_key');
        $user->redmine_base_url = $request->input('redmine_base_url') ?: $originalBaseUrl;

        try {
            $isValid = $this->redmineService->validateKey($user);

            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! Your Redmine API key is valid.',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed. Please check your API key and base URL.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } finally {
            $user->redmine_api_key = $originalApiKey;
            $user->redmine_base_url = $originalBaseUrl;
        }
    }

    public function clearRedmineKey(Request $request): JsonResponse
    {
        $request->user()->update([
            'redmine_api_key' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Redmine API key cleared successfully.',
        ], Response::HTTP_OK);
    }

    public function testSlackKey(Request $request): JsonResponse
    {
        $request->validate([
            'slack_user_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        $user->slack_user_id = $request->input('slack_user_id');

        try {
            $isValid = $this->slackService->validateKey($user);
            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! Your Slack API key is valid.',
                ], Response::HTTP_OK);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed. Please check your API key.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function clearSlackKey(Request $request): JsonResponse
    {
        $request->user()->update([
            'slack_user_id' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slack API key cleared successfully.',
        ], Response::HTTP_OK);
    }

    public function clearTelegramKey(Request $request): JsonResponse
    {
        $request->user()->update([
            'telegram_user_id' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram API key cleared successfully.',
        ], Response::HTTP_OK);
    }
}
