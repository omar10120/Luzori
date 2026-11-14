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
            'otp_phones' => 'nullable|array',
            'otp_phones.*' => 'nullable|string|max:30',
            'emirate'        => 'nullable|string|max:255',
            'tax_number'     => 'nullable|string|max:255',
            'auto_print_sale'=> 'boolean',
            'sms_allow'      => 'boolean',
            'allow_notification'      => 'boolean',

        ];
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('otp_phones');
        if (is_string($raw)) {
            $phones = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($raw)) {
            $phones = $raw;
        } else {
            $phones = [];
        }

        $phones = array_values(array_unique(array_filter(array_map(function ($phone) {
            return trim((string) $phone);
        }, $phones))));

        $this->merge([
            'otp_phones' => $phones,
            'auto_print_sale' => $this->boolean('auto_print_sale'),
            'sms_allow' => $this->boolean('sms_allow'),
        ]);
    }
}
