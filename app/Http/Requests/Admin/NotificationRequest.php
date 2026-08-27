<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'target_type' => 'required|in:users,centers',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:51200',
            'title' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:1000',
        ];

        foreach (Config::get('translatable.locales', ['ar', 'en']) as $locale) {
            $rules[$locale . '.title'] = 'nullable|string|max:150';
            $rules[$locale . '.text'] = 'nullable|string|max:1000';
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasLocale = false;
            foreach (Config::get('translatable.locales', ['ar', 'en']) as $locale) {
                if ($this->input($locale . '.title')) {
                    $hasLocale = true;
                    break;
                }
            }
            if (!$hasLocale && !$this->input('title')) {
                $validator->errors()->add('title', __('validation.required', ['attribute' => __('field.title')]));
            }
        });
    }
}
