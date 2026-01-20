<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class PageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
                'privacy_policy_' . $locale => $this->$locale['privacy_policy'],
                'terms_conditions_' . $locale => $this->$locale['terms_conditions'],
                'about_us_' . $locale => $this->$locale['about_us']
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
                'privacy_policy_' . $locale => 'required',
                'terms_conditions_' . $locale => 'required',
                'about_us_' . $locale => 'required'
            ]);
        }
        return $locales;
    }
}
