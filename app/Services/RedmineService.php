<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedmineService
{
    protected const API_TIMEOUT = 30;
    protected const PER_PAGE = 100;

    public function validateKey(User $user): bool
    {
        if (! $user->redmine_api_key) {
            return false;
        }

        try {
            $response = $this->makeRequest($user, 'GET', '/users/current.json');

            return $response->successful() &&
                $response->json('user.id') !== null;
        } catch (\Exception $e) {
            Log::warning('Redmine API key validation failed', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function fetchProjects(User $user): Collection
    {
        if (! $user->redmine_api_key) {
            throw new \InvalidArgumentException('User does not have a Redmine API key configured');
        }

        $entries = collect();
        $offset = 0;
        $hasMore = true;

        while ($hasMore) {
            try {
                $response = $this->makeRequest($user, 'GET', '/projects.json', [
                    'limit' => self::PER_PAGE,
                ]);

                if (! $response->successful()) {
                    Log::error('Failed to fetch Redmine projects', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    break;
                }

                $data = $response->json();
                $projects = $data['projects'] ?? [];
                if (empty($projects)) {
                    $hasMore = false;
                } else {
                    foreach ($projects as $project) {
                        $entries->push([
                            'redmine_id' => $project['id'],
                            'name' => $project['name'],
                            'identifier' => $project['identifier'],
                            'description' => $project['description'] ?? '',
                            'homepage' => $project['homepage'] ?? '',
                            'is_public' => $project['is_public'],
                        ]);
                    }

                    $offset += self::PER_PAGE;
                    $hasMore = count($projects) === self::PER_PAGE;
                }
            } catch (\Exception $e) {
                Log::error('Error fetching Redmine projects', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                break;
            }
        }

        return $entries;
    }

    public function fetchTimeEntries(User $user, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        if (! $user->redmine_api_key) {
            throw new \InvalidArgumentException('User does not have a Redmine API key configured');
        }

        $entries = collect();
        $offset = 0;
        $hasMore = true;

        while ($hasMore) {
            try {
                $params = [
                    'user_id' => 'me',
                    'limit' => self::PER_PAGE,
                    'offset' => $offset,
                    'include' => 'project,issue,activity,user',
                ];

                if ($from) {
                    $params['from'] = $from->format('Y-m-d');
                }

                if ($to) {
                    $params['to'] = $to->format('Y-m-d');
                }

                $response = $this->makeRequest($user, 'GET', '/time_entries.json', $params);

                if (! $response->successful()) {
                    Log::error('Failed to fetch Redmine time entries', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    break;
                }

                $data = $response->json();
                $timeEntries = $data['time_entries'] ?? [];

                if (empty($timeEntries)) {
                    $hasMore = false;
                } else {
                    foreach ($timeEntries as $entry) {
                        $entries->push([
                            'redmine_id' => $entry['id'],
                            'project_id' => $entry['project']['id'],
                            'activity_id' => $entry['activity']['id'],
                            'activity_name' => $entry['activity']['name'],
                            'hours' => $entry['hours'],
                            'spent_on' => $entry['spent_on'],
                            'comments' => $entry['comments'] ?? '',
                        ]);
                    }

                    $offset += self::PER_PAGE;
                    $hasMore = count($timeEntries) === self::PER_PAGE;
                }
            } catch (\Exception $e) {
                Log::error('Error fetching Redmine time entries', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'offset' => $offset,
                ]);
                break;
            }
        }

        return $entries;
    }

    public function createTimeEntry(User $user, array $payload): Response
    {
        if (! $user->redmine_api_key) {
            throw new \InvalidArgumentException('User does not have a Redmine API key configured');
        }

        $timeEntry = [
            'time_entry' => [
                'issue_id' => $payload['issue_id'] ?? null,
                'project_id' => $payload['project_id'] ?? null,
                'spent_on' => $payload['date'] ?? Carbon::today()->format('Y-m-d'),
                'hours' => $payload['hours'],
                'activity_id' => $payload['activity_id'] ?? 1,
                'comments' => $payload['comments'] ?? '',
            ],
        ];

        if (empty($timeEntry['time_entry']['issue_id']) && empty($timeEntry['time_entry']['project_id'])) {
            throw new \InvalidArgumentException('Either issue_id or project_id must be provided');
        }

        try {
            $response = $this->makeRequest($user, 'POST', '/time_entries.json', $timeEntry);

            if ($response->successful()) {
                Log::info('Time entry created successfully', [
                    'user_id' => $user->id,
                    'hours' => $payload['hours'],
                    'date' => $payload['date'] ?? 'today',
                ]);
            } else {
                Log::error('Failed to create Redmine time entry', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'payload' => $payload,
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Error creating Redmine time entry', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            throw $e;
        }
    }

    protected function makeRequest(User $user, string $method, string $endpoint, array $data = []): Response
    {
        $baseUrl = $this->resolveBaseUrl($user);
        $url = rtrim($baseUrl, '/') . $endpoint;

        $httpClient = Http::timeout(self::API_TIMEOUT)
            ->withHeaders([
                'X-Redmine-API-Key' => $user->redmine_api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

        return match (strtoupper($method)) {
            'GET' => $httpClient->get($url, $data),
            'POST' => $httpClient->post($url, $data),
            'PUT' => $httpClient->put($url, $data),
            'DELETE' => $httpClient->delete($url, $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }

    protected function resolveBaseUrl(User $user): string
    {
        if (! empty($user->redmine_base_url)) {
            return $user->redmine_base_url;
        }

        if (! empty($user->tenant?->redmine_base_url)) {
            return $user->tenant->redmine_base_url;
        }

        $envUrl = config('services.redmine.base_url') ?? env('REDMINE_BASE_URL');
        if (empty($envUrl)) {
            throw new \RuntimeException('No Redmine base URL configured for user, tenant, or environment');
        }

        return $envUrl;
    }
}
