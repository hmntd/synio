<?php

namespace App\Jobs;

use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\SlackService;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendUserNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public NotificationSetting $setting,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SlackService $slack, TelegramService $telegram): void
    {
        $isDaily = ($this->setting->frequency === 'daily');

        $periodLabel = $isDaily ? 'today' : 'this week';

        $worked = $isDaily
            ? ($this->user->daily_hours ?? 0)
            : ($this->user->weekly_hours ?? ($this->user->daily_hours ?? 0) * 5);

        $target = $isDaily
            ? ($this->user->daily_hours_target ?? 0)
            : (($this->user->daily_hours_target ?? 0) * 5);

        $message = "Hi {$this->user->name}, you haven’t reached your {$this->setting->frequency} goal "
            . "({$periodLabel}: {$worked}/{$target}h). Keep it up!";

        if (! empty($this->user->slack_provided)) {
            $slack->sendMessage($this->user, $message);
        }

        if (! empty($this->user->telegram_provided)) {
            $telegram->sendMessage($this->user, $message);
        }
    }
}
