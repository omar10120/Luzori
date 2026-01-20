<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InfoRequest extends FormRequest
{
    /**
     * Determine if the admin is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Allow a minimal payload when only updating the sender email from permissions modal
        if ($this->has('email_only') && $this->boolean('email_only')) {
            return [
                'id' => 'required|exists:infos,id',
                'email' => 'required|email',
            ];
        }

        return [
            'id' => 'required|exists:infos,id',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits_between:6,10',
            'facebook' => 'required|url',
            'linkedin' => 'required|url',
            'instagram' => 'required|url',
            'twitter' => 'required|url',
            'whatsapp' => 'required|url',
            'youtube' => 'required|url',
        ];
    }
}
