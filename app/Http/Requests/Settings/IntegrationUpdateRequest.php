<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'redmine_base_url' => ['nullable', 'string', 'url', 'max:255'],
            'redmine_api_key' => ['nullable', 'string', 'max:255'],
            'slack_user_id' => ['nullable', 'string', 'max:255', 'regex:/^[A-Z0-9]+$/'],
            'telegram_user_id' => ['nullable', 'string', 'max:255', 'regex:/^-?\d+$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'redmine_base_url.url' => 'The Redmine base URL must be a valid URL.',
            'slack_user_id.regex' => 'The Slack User ID must contain only uppercase letters and numbers (e.g., U01234ABCD).',
            'telegram_chat_id.regex' => 'The Telegram Chat ID must be a valid number (e.g., 123456789 or -123456789).',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'redmine_base_url' => 'Redmine base URL',
            'redmine_api_key' => 'Redmine API key',
            'slack_user_id' => 'Slack User ID',
            'telegram_chat_id' => 'Telegram Chat ID',
        ];
    }
}
