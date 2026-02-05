<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class CategoryRequest extends FormRequest
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
                'name_' . $locale => $this->$locale['name'] ?? null,
                'description_' . $locale => $this->$locale['description'] ?? null,
                'keywords_' . $locale => $this->$locale['keywords'] ?? null,
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
                'name_' . $locale => 'required',
                'description_' . $locale => 'nullable',
                'keywords_' . $locale => 'nullable',
            ]);
        }

        $validations = [
            'parent_id' => 'nullable|exists:categories_services,id',
        ];

        if (isset($this->id)) {
            $validations['id'] = 'required|exists:categories_services,id';
            // Prevent setting parent to self or descendant
            $validations['parent_id'] = 'nullable|exists:categories_services,id|not_in:' . $this->id;
        }

        return array_merge($locales, $validations);
    }
}
