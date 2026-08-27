<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FirebaseSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_account_json' => 'nullable|string',
            'api_key' => 'nullable|string|max:255',
            'auth_domain' => 'nullable|string|max:255',
            'project_id' => 'nullable|string|max:255',
            'storage_bucket' => 'nullable|string|max:255',
            'messaging_sender_id' => 'nullable|string|max:255',
            'app_id' => 'nullable|string|max:255',
            'measurement_id' => 'nullable|string|max:255',
        ];
    }
}
