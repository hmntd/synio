<?php

namespace App\DTOs;

use Carbon\Carbon;

class RedmineDto
{
    public function __construct(
        public readonly int $id,
        public readonly float $hours,
        public readonly Carbon $date,
        public readonly string $projectName,
        public readonly ?int $issueId,
        public readonly ?string $issueName,
        public readonly string $activityName,
        public readonly string $comments,
        public readonly int $userId,
        public readonly array $raw = [],
    ) {}

    public static function fromApiResponse(array $entry): self
    {
        $spentOn = isset($entry['spent_on']) 
            ? Carbon::parse($entry['spent_on'])
            : Carbon::now();

        return new self(
            id: $entry['id'],
            hours: (float) $entry['hours'],
            date: $spentOn,
            projectName: $entry['project']['name'] ?? 'Unknown Project',
            issueId: $entry['issue']['id'] ?? null,
            issueName: $entry['issue']['subject'] ?? null,
            activityName: $entry['activity']['name'] ?? 'Development',
            comments: $entry['comments'] ?? '',
            userId: $entry['user']['id'] ?? 0,
            raw: $entry,
        );
    }
    
    public function getProjectName(): string
    {
        return $this->projectName;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'hours' => $this->hours,
            'date' => $this->date->format('Y-m-d'),
            'project_name' => $this->projectName,
            'issue_id' => $this->issueId,
            'issue_name' => $this->issueName,
            'activity_name' => $this->activityName,
            'comments' => $this->comments,
            'user_id' => $this->userId,
        ];
    }

    public function hasIssue(): bool
    {
        return $this->issueId !== null;
    }

    public function getDisplayName(): string
    {
        if ($this->hasIssue()) {
            return "#{$this->issueId}: {$this->issueName}";
        }
        
        return $this->projectName;
    }

    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    public function isThisWeek(): bool
    {
        return $this->date->isCurrentWeek();
    }

    public function isThisMonth(): bool
    {
        return $this->date->isCurrentMonth();
    }
}