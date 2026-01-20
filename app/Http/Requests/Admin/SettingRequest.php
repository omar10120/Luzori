<?php

namespace App\Http\Requests\Admin;

use App\Enums\PageEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class SettingRequest extends FormRequest
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
                'WebsiteName_' . $locale => $this->$locale[PageEnum::WebsiteName->value],
                'WebsiteTitle_' . $locale => $this->$locale[PageEnum::WebsiteTitle->value],
                'Auther_' . $locale => $this->$locale[PageEnum::Auther->value],
                'WebsiteDescription_' . $locale => $this->$locale[PageEnum::WebsiteDescription->value],
                'Address_' . $locale => $this->$locale[PageEnum::Address->value],
                'FooterText_' . $locale => $this->$locale[PageEnum::FooterText->value],
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
                'WebsiteName_' . $locale => 'required|string',
                'WebsiteTitle_' . $locale => 'required|string',
                'Auther_' . $locale => 'required|string',
                'WebsiteDescription_' . $locale => 'required|string',
                'Address_' . $locale => 'required|string',
                'FooterText_' . $locale => 'required|string',
            ]);
        }

        $validations = [
            'language' => 'required',
            'tips' => 'required',
            'invoice_info' => 'required',
            'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
        ];

        return array_merge($locales, $validations);
    }
}
