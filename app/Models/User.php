<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'slack_user_id',
        'telegram_user_id',
        'redmine_api_key',
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
     * Get the tenant that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
