<?php

namespace App\Http\Requests\CenterAPI\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class PackageRequest extends FormRequest
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

        if (isset($this->id)) {
            $validations = [
                'id' => 'required|exists:packages,id,deleted_at,NULL',
                'paid_services' => 'required|array',
                'paid_services.*' => 'required|exists:services,id,deleted_at,NULL',
                'free_services' => 'required|array',
                'free_services.*' => 'required|exists:services,id,deleted_at,NULL',
            ];
        } else {
            $validations = [
                'paid_services' => 'required|array',
                'paid_services.*' => 'required|exists:services,id,deleted_at,NULL',
                'free_services' => 'required|array',
                'free_services.*' => 'required|exists:services,id,deleted_at,NULL',
            ];
        }
        return array_merge($locales, $validations);
    }
}
