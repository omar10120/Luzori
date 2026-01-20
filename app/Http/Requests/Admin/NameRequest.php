<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class NameRequest extends FormRequest
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        foreach (Config::get('translatable.locales') as $locale) {
            $this->merge([
                'name_' . $locale => $this->$locale['name']
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
                'name_' . $locale => 'required'
            ]);
        }
        return $locales;
    }
}
