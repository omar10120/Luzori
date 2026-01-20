<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class ServiceRequest extends FormRequest
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
                'name_' . $locale => $this->$locale['name']
            ]);
        }

        if (isset($this->is_top)) {
            $this->merge([
                'is_top' => 1
            ]);
        } else {
            $this->merge([
                'is_top' => 0
            ]);
        }

        if (isset($this->has_commission)) {
            $this->merge([
                'has_commission' => 1
            ]);
        } else {
            $this->merge([
                'has_commission' => 0
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
                'id' => 'required|exists:services,id',
                'rooms_no' => 'required|numeric',
                'free_book' => 'required|numeric',
                'price' => 'required|numeric',
                'is_top' => 'required|boolean',
                'has_commission' => 'required|boolean',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            $validations = [
                'rooms_no' => 'required|numeric',
                'free_book' => 'required|numeric',
                'price' => 'required|numeric',
                'is_top' => 'required|boolean',
                'has_commission' => 'required|boolean',
                'image' => 'required|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
        return array_merge($locales, $validations);
    }
}
