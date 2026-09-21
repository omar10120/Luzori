<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->filled('id');

        return [
            'id' => ['nullable', 'integer', 'exists:central.banners,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:4096'],
            'placement_type' => ['required', Rule::in(['home', 'mobile', 'web', 'sidebar', 'checkout', 'category_page', 'service_page'])],
            'link_type' => ['required', Rule::in(['external', 'category', 'service', 'center'])],
            'external_url' => ['nullable', 'url', 'max:2048', 'required_if:link_type,external'],
            'center_id' => ['nullable', 'integer', 'exists:central.centers,id', 'required_if:link_type,service,center'],
            'global_category_id' => ['nullable', 'integer', 'exists:central.global_categories,id', 'required_if:link_type,category'],
            'service_id' => ['nullable', 'integer', 'required_if:link_type,service'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $linkType = $this->input('link_type');

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'center_id' => $linkType === 'service' || $linkType === 'center' ? $this->input('center_id') : null,
            'global_category_id' => $linkType === 'category' ? $this->input('global_category_id') : null,
            'service_id' => $linkType === 'service' ? $this->input('service_id') : null,
            'external_url' => $linkType === 'external' ? $this->input('external_url') : null,
        ]);
    }
}
