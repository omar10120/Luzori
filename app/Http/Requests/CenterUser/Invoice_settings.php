<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class Invoice_settingsRequest extends FormRequest
{
    /**
     * Determine if the invoice settings is authorized to make this request.
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
        return [
            'phone_number_1' => 'nullable|string|max:255',
            'phone_number_2' => 'nullable|string|max:255',
            'phone_number_3' => 'nullable|string|max:255',
            'emirate' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
        ];
    }
}
