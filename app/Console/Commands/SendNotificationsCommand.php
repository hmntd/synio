<?php

namespace App\Console\Commands;

use App\Jobs\SendUserNotification;
use App\Models\NotificationSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $settings = NotificationSetting::where('enabled', true)->get();
        foreach ($settings as $setting) {
            $user = $setting->user;

            if (! $user) continue;

            if (! $user->redmine_api_key) continue;

            if (! $user->slack_user_id && ! $user->telegram_chat_id) continue;

            $userTime = $now->copy()->setTimezone($user->timezone);

            if ($userTime->format('H:i') !== Carbon::parse($setting->send_at)->format('H:i')) continue;

            if ($setting->frequency === 'weekly' && strtolower($userTime->englishDayOfWeek) !== $setting->day_of_week) continue;

            if ($user->daily_hours >= $user->daily_hours_target) continue;

            dispatch(new SendUserNotification($user, $setting));
        }

        return Command::SUCCESS;
    }
}
