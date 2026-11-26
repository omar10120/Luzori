<?php

namespace App\Services;

use App\Models\Invoice_Settings;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class SaleOtpService
{
    public const SESSION_KEY = 'sale_otp_verification';

    public function __construct(private SMSGatewayService $smsGateway)
    {
    }

    /**
     * Request a sale OTP via the configured channel (sms | email).
     *
     * Behavior:
     *  - 'sms'   → sends to SMSGatewayService::getAdminPhones()
     *              If phones are empty OR every SMS fails → falls back to email.
     *  - 'email' → sends to Invoice_Settings.emails
     *              If emails are empty OR email fails → falls back to SMS.
     *  - Both empty → returns success=false with a clear message.
     *
     * @param  string  $channel  'sms' | 'email' (anything else is coerced to 'sms')
     */
    public function requestOtp(
        Sale $sale,
        string $action,
        string $reason,
        ?string $newDate = null,
        string $channel = 'sms'
    ): array {
        $action = strtolower($action);
        if (!in_array($action, ['delete', 'edit'], true)) {
            return ['success' => false, 'message' => __('general.invalid_action')];
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 3 || mb_strlen($reason) > 200) {
            return ['success' => false, 'message' => __('general.reason_length_invalid')];
        }

        if ($action === 'edit' && empty($newDate)) {
            return ['success' => false, 'message' => __('general.date_required')];
        }

        // Coerce unknown channels to the safe default
        $channel = in_array($channel, ['sms', 'email'], true) ? $channel : 'sms';

        $code        = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $locale      = app()->getLocale();
        $actionLabel = $action === 'delete' ? __('general.delete') : __('general.edit');

        $message = __('general.sms_sale_otp', [
            'action'  => $actionLabel,
            'sale_id' => $sale->id,
            'reason'  => $reason,
            'code'    => $code,
        ], $locale);

        // ── Dispatch on channel (with mutual fallback) ─────────────────────
        $delivery = $channel === 'email'
            ? $this->sendViaEmail($sale, $message, $locale)
            : $this->sendViaSms($sale, $message, $locale);

        if (!$delivery['success']) {
            return ['success' => false, 'message' => $delivery['message']];
        }

        // The channel actually used may differ from the configured one if a
        // fallback kicked in — record the effective channel in the session.
        $effectiveChannel  = $delivery['channel'];
        $maskedContacts    = $delivery['masked_contacts'];

        Session::put(self::SESSION_KEY, [
            'sale_id'   => $sale->id,
            'action'    => $action,
            'reason'    => $reason,
            'new_date'  => $newDate,
            'code'      => $code,
            'channel'   => $effectiveChannel,   // ← audit trail
            'expires'   => now()->addMinutes(10)->timestamp,
        ]);

        return [
            'success'         => true,
            'message'         => __('general.otp_sent_successfully'),
            'channel'         => $effectiveChannel,
            'masked_contacts' => $maskedContacts,
            // Legacy keys (kept so the existing JS doesn't need to change):
            'masked_phones'   => $effectiveChannel === 'sms'   ? $maskedContacts : [],
            'masked_emails'   => $effectiveChannel === 'email' ? $maskedContacts : [],
        ];
    }

    public function verifyPending(string $code, int $saleId, string $action): array
    {
        $pending = Session::get(self::SESSION_KEY);
        if (!$pending) {
            return ['success' => false, 'message' => __('general.otp_not_found')];
        }

        if (($pending['expires'] ?? 0) < now()->timestamp) {
            Session::forget(self::SESSION_KEY);
            return ['success' => false, 'message' => __('general.otp_expired')];
        }

        if ((int) ($pending['sale_id'] ?? 0) !== (int) $saleId || ($pending['action'] ?? '') !== $action) {
            return ['success' => false, 'message' => __('general.otp_mismatch')];
        }

        if (trim($code) !== (string) ($pending['code'] ?? '')) {
            return ['success' => false, 'message' => __('general.otp_invalid')];
        }

        return ['success' => true, 'pending' => $pending];
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function getAdminPhones(): array
    {
        return $this->smsGateway->getAdminPhones();
    }

    /**
     * Return admin email recipients from Invoice_Settings.emails.
     * Case-insensitive, trimmed, deduplicated, invalid entries filtered out.
     */
    public function getAdminEmails(): array
    {
        $recipients = [];
        try {
            $settings = Invoice_Settings::query()->first();
            $emails   = $settings?->emails ?? [];
            if (is_array($emails)) {
                foreach ($emails as $email) {
                    $email = is_string($email) ? trim($email) : '';
                    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $recipients[] = $email;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Sale OTP: failed to read invoice_settings.emails: ' . $e->getMessage());
        }

        return array_values(array_unique(array_map('strtolower', $recipients)));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private delivery helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Send OTP via SMS. Falls back to email on empty phone list or all-failed.
     */
    private function sendViaSms(Sale $sale, string $message, string $locale): array
    {
        $phones = $this->getAdminPhones();

        if (empty($phones)) {
            Log::warning('Sale OTP: channel=sms but no admin phones configured; falling back to email', [
                'sale_id' => $sale->id,
            ]);
            return $this->sendViaEmail($sale, $message, $locale);
        }

        $sent = false;
        foreach ($phones as $phone) {
            $result = $this->smsGateway->sendSMS($phone, $message, $locale === 'ar' ? 2 : 1);
            if (!empty($result['success'])) {
                $sent = true;
            } else {
                Log::warning('Sale OTP SMS failed', [
                    'sale_id' => $sale->id,
                    'phone'   => $phone,
                    'result'  => $result,
                ]);
            }
        }

        if (!$sent) {
            Log::warning('Sale OTP: all SMS deliveries failed; falling back to email', [
                'sale_id' => $sale->id,
            ]);
            return $this->sendViaEmail($sale, $message, $locale);
        }

        return [
            'success'         => true,
            'channel'         => 'sms',
            'masked_contacts' => array_map([$this, 'maskPhone'], $phones),
        ];
    }

    /**
     * Send OTP via email. Falls back to SMS on empty recipient list or failure.
     */
    private function sendViaEmail(Sale $sale, string $message, string $locale): array
    {
        $recipients = $this->getAdminEmails();

        if (empty($recipients)) {
            Log::warning('Sale OTP: channel=email but no recipients configured; falling back to SMS', [
                'sale_id' => $sale->id,
            ]);
            return $this->sendViaSmsOnly($sale, $message, $locale);
        }

        $subject = __('general.sale_otp_email_subject');
        if (!is_string($subject) || $subject === '' || str_starts_with($subject, 'general.')) {
            $subject = 'Sale OTP Verification';
        }

        try {
            Mail::raw($message, function ($mail) use ($recipients, $subject) {
                $mail->to($recipients)->subject($subject);
            });

            Log::info('Sale OTP sent via email', [
                'sale_id'    => $sale->id,
                'recipients' => $recipients,
            ]);

            return [
                'success'         => true,
                'channel'         => 'email',
                'masked_contacts' => array_map([$this, 'maskEmail'], $recipients),
            ];
        } catch (\Throwable $e) {
            Log::error('Sale OTP email failed: ' . $e->getMessage(), [
                'sale_id'    => $sale->id,
                'recipients' => $recipients,
            ]);
            // Try SMS as last resort
            return $this->sendViaSmsOnly($sale, $message, $locale);
        }
    }

    /**
     * SMS-only send (used as fallback path from sendViaEmail). Unlike
     * sendViaSms(), this does NOT fall back to email — that would loop.
     */
    private function sendViaSmsOnly(Sale $sale, string $message, string $locale): array
    {
        $phones = $this->getAdminPhones();
        if (empty($phones)) {
            Log::error('Sale OTP: no SMS phones AND no email recipients available — OTP cannot be delivered', [
                'sale_id' => $sale->id,
            ]);
            return [
                'success' => false,
                'message' => __('general.no_admin_phone'),
            ];
        }

        $sent = false;
        foreach ($phones as $phone) {
            $result = $this->smsGateway->sendSMS($phone, $message, $locale === 'ar' ? 2 : 1);
            if (!empty($result['success'])) {
                $sent = true;
            } else {
                Log::warning('Sale OTP SMS failed (fallback path)', [
                    'sale_id' => $sale->id,
                    'phone'   => $phone,
                    'result'  => $result,
                ]);
            }
        }

        if (!$sent) {
            return [
                'success' => false,
                'message' => __('general.sms_send_failed'),
            ];
        }

        return [
            'success'         => true,
            'channel'         => 'sms',
            'masked_contacts' => array_map([$this, 'maskPhone'], $phones),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Masking helpers
    // ─────────────────────────────────────────────────────────────────────

    private function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return substr($phone, 0, 2) . str_repeat('*', max(0, $len - 4)) . substr($phone, -2);
    }

    private function maskEmail(string $email): string
    {
        $atPos = strpos($email, '@');
        if ($atPos === false || $atPos < 1) {
            return str_repeat('*', strlen($email));
        }

        $local  = substr($email, 0, $atPos);
        $domain = substr($email, $atPos);
        $keep   = min(2, max(1, strlen($local) - 1));

        return substr($local, 0, $keep)
             . str_repeat('*', max(1, strlen($local) - $keep))
             . $domain;
    }
}