<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,
        Notifiable,
        TwoFactorAuthenticatable,
        BelongsToTenant,
        HasApiTokens,
        HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'slack_user_id',
        'telegram_user_id',
        'redmine_base_url',
        'redmine_api_key',
        'name',
        'email',
        'password',
        'daily_hours_target',
        'send_time',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be append for serialization.
     *
     * @var list<string>
     */
    protected $appends = [
        'redmine_api_provided',
        'slack_provided',
        'telegram_provided',
        'daily_hours',
        'weekly_hours',
        'monthly_hours',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Listen for the User created event and create a default notification setting.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function (User $user) {
            NotificationSetting::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'frequency' => 'daily',
                'send_at' => '18:00',
                'enabled' => true,
            ]);
        });
    }

    /**
     * Whether the user has provided their Redmine API key.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function redmineApiProvided(): Attribute
    {
        return Attribute::get(fn() => !is_null($this->redmine_api_key));
    }

    /**
     * Whether the user has provided their Slack User ID.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function slackProvided(): Attribute
    {
        return Attribute::get(fn() => !empty($this->slack_user_id));
    }

    /**
     * Whether the user has provided their Telegram User ID.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function telegramProvided(): Attribute
    {
        return Attribute::get(fn() => !empty($this->telegram_user_id));
    }

    /**
     * The total hours the user has worked today.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function dailyHours(): Attribute
    {
        return Attribute::get(fn() => $this->timeEntries()->whereDate('spent_on', today($this->timezone))->sum('hours'));
    }

    /**
     * The total hours the user has worked this week.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function weeklyHours(): Attribute
    {
        return Attribute::get(function () {
            $tz = $this->timezone ?? config('app.timezone');

            $startOfWeek = now($tz)->startOfWeek();
            $endOfWeek = now($tz)->endOfWeek();

            return $this->timeEntries()
                ->whereBetween('spent_on', [$startOfWeek, $endOfWeek])
                ->sum('hours');
        });
    }

    /**
     * The total hours the user has worked this month.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function monthlyHours(): Attribute
    {
        return Attribute::get(function () {
            $tz = $this->timezone ?? config('app.timezone');

            $startOfMonth = now($tz)->startOfMonth();
            $endOfMonth = now($tz)->endOfMonth();

            return $this->timeEntries()
                ->whereBetween('spent_on', [$startOfMonth, $endOfMonth])
                ->sum('hours');
        });
    }

    /**
     * Get the tenant that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the projects that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the time entries that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Get the notification settings that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notificationSettings(): HasOne
    {
        return $this->hasOne(NotificationSetting::class);
    }

    /**
     * Get the activity logs that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id', 'id');
    }

    /**
     * Get the mentees that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mentees(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            Mentorship::class,
            'mentor_id',
            'id',
            'id',
            'mentee_id',
        );
    }
}
