<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class NotificationRequest extends FormRequest
{
    /**
     * Determine if the center is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        foreach (Config::get('translatable.locales') as $locale) {
            $this->merge([
                'title_' . $locale => $this->$locale['title'],
                'text_' . $locale => $this->$locale['text']
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $locales = [];
        foreach (Config::get('translatable.locales') as $locale) {
            $locales = array_merge($locales, [
                'title_' . $locale => 'required|max:50',
                'text_' . $locale => 'required|max:200',
            ]);
        }

        $validations = [
            'users' => 'required|array'
        ];
        return array_merge($locales, $validations);
    }
}
