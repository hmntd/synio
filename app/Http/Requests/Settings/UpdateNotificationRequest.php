<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationRequest extends FormRequest
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
            'frequency' => ['required', 'string', 'max:255', 'in:daily,weekly'],
            'time' => ['required', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
            'day_of_week' => ['required_if:frequency,weekly', 'string', 'max:255', 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
        ];
    }
}
