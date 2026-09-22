<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number_1' => 'nullable|string|max:255',
            'phone_number_2' => 'nullable|string|max:255',
            'phone_number_3' => 'nullable|string|max:255',
            'emirate'        => 'nullable|string|max:255',
            'tax_number'     => 'nullable|string|max:255',
            'auto_print_sale'=> 'boolean',
            'sms_allow'      => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auto_print_sale' => $this->boolean('auto_print_sale'),
            'sms_allow' => $this->boolean('sms_allow'),
        ]);
    }
}
