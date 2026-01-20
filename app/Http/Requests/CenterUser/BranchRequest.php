<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class BranchRequest extends FormRequest
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
                'name_' . $locale => $this->$locale['name'],
                'city_' . $locale => $this->$locale['city'],
                'address_' . $locale => $this->$locale['address']
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
                'city_' . $locale => 'required',
                'address_' . $locale => 'required',
            ]);
        }

        if (isset($this->id)) {
            $validations = [
                'id' => 'required|exists:branches,id',
                'longitude' => 'required',
                'latitude' => 'required'
            ];
        } else {
            $validations = [
                'longitude' => 'required',
                'latitude' => 'required'
            ];
        }
        return array_merge($locales, $validations);
    }
}
