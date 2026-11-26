<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

            'emirate'            => 'nullable|string|max:255',
            'tax_number'         => 'nullable|string|max:255',
            'auto_print_sale'    => 'boolean',
            'sms_allow'          => 'boolean',
            'allow_notification' => 'boolean',

            // OTP delivery channel
            'otp_channel'  => ['required', 'in:email,sms'],

            // Both arrays are always accepted; only the active one is required
            'otp_phones'   => ['array'],
            'otp_phones.*' => ['nullable', 'string', 'max:20'],

            'emails'       => ['array'],
            'emails.*'     => ['nullable', 'email', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                // Don't pile conditional errors on top of structural ones
                return;
            }

            $channel = $this->input('otp_channel', 'sms');

            if ($channel === 'sms') {
                $phones = array_filter(
                    $this->input('otp_phones', []),
                    fn ($p) => is_string($p) && trim($p) !== ''
                );
                if (empty($phones)) {
                    $v->errors()->add(
                        'otp_phones',
                        __('validation.required', ['attribute' => __('field.otp_phones')])
                    );
                }
            }

            if ($channel === 'email') {
                $emails = array_filter(
                    $this->input('emails', []),
                    fn ($e) => is_string($e) && trim($e) !== ''
                );
                if (empty($emails)) {
                    $v->errors()->add(
                        'emails',
                        __('validation.required', ['attribute' => __('field.email_receivers')])
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // ---- OTP phones (accept string or array) ----
        $raw = $this->input('otp_phones');
        if (is_string($raw)) {
            $phones = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($raw)) {
            $phones = $raw;
        } else {
            $phones = [];
        }

        $phones = array_values(array_unique(array_filter(array_map(
            fn ($phone) => trim((string) $phone),
            $phones
        ))));

        // ---- Emails: trim + drop empties + dedupe (server-side mirror of JS) ----
        $rawEmails = $this->input('emails', []);
        if (is_string($rawEmails)) {
            $rawEmails = preg_split('/[\s,;]+/', $rawEmails, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (!is_array($rawEmails)) {
            $rawEmails = [];
        }

        $emails = array_values(array_unique(array_filter(array_map(
            fn ($e) => is_string($e) ? strtolower(trim($e)) : '',
            $rawEmails
        ))));

        // ---- Default otp_channel so legacy/API callers don't fail "required" ----
        $channel = $this->input('otp_channel');
        if (!in_array($channel, ['email', 'sms'], true)) {
            $channel = 'sms';
        }

        $this->merge([
            'otp_phones'         => $phones,
            'emails'             => $emails,
            'otp_channel'        => $channel,
            'auto_print_sale'    => $this->boolean('auto_print_sale'),
            'sms_allow'          => $this->boolean('sms_allow'),
            'allow_notification' => $this->boolean('allow_notification'),
        ]);
    }
}