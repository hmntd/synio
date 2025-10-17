<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    protected const API_TIMEOUT = 30;
    protected const API_BASE = 'https://slack.com/api';

    public function validateKey(User $user): bool
    {
        if (!$user->slack_user_id) {
            return false;
        }

        try {
            $response = $this->makeRequest($user, 'GET', '/auth.test');

            return $response->successful()
                && $response->json('user_id') !== null;
        } catch (\Exception $e) {
            Log::warning('Slack token validation failed', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function makeRequest(User $user, string $method, string $endpoint, array $data = []): ClientResponse
    {
        $url = rtrim(self::API_BASE, '/') . $endpoint;

        $httpClient = Http::timeout(self::API_TIMEOUT)
            ->withToken(config('services.slack.notifications.bot_token'))
            ->acceptJson();

        return match (strtoupper($method)) {
            'GET' => $httpClient->get($url, $data),
            'POST' => $httpClient->post($url, $data),
            'PUT' => $httpClient->put($url, $data),
            'DELETE' => $httpClient->delete($url, $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }

    public function sendMessage(User $user, string $message): bool
    {
        if (!$user->slack_user_id) {
            return false;
        }

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'actor_id' => $user->id,
            'action' => 'sent slack message',
        ]);

        try {
            $response = $this->makeRequest($user, 'POST', '/chat.postMessage', [
                'channel' => $user->slack_user_id,
                'text' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Slack message sending failed', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
