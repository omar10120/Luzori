<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SaleOtpService
{
    public const SESSION_KEY = 'sale_otp_verification';

    public function __construct(private SMSGatewayService $smsGateway)
    {
    }

    public function requestOtp(Sale $sale, string $action, string $reason, ?string $newDate = null): array
    {
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

        $phones = $this->getAdminPhones();
        if (empty($phones)) {
            return ['success' => false, 'message' => __('general.no_admin_phone')];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $locale = app()->getLocale();
        $actionLabel = $action === 'delete' ? __('general.delete') : __('general.edit');

        $message = __('general.sms_sale_otp', [
            'action' => $actionLabel,
            'sale_id' => $sale->id,
            'reason' => $reason,
            'code' => $code,
        ], $locale);

        $sent = false;
        foreach ($phones as $phone) {
            $result = $this->smsGateway->sendSMS($phone, $message, $locale === 'ar' ? 2 : 1);
            if (!empty($result['success'])) {
                $sent = true;
            } else {
                Log::warning('Sale OTP SMS failed', [
                    'sale_id' => $sale->id,
                    'phone' => $phone,
                    'result' => $result,
                ]);
            }
        }

        if (!$sent) {
            return ['success' => false, 'message' => __('general.sms_send_failed')];
        }

        Session::put(self::SESSION_KEY, [
            'sale_id' => $sale->id,
            'action' => $action,
            'reason' => $reason,
            'new_date' => $newDate,
            'code' => $code,
            'expires' => now()->addMinutes(10)->timestamp,
        ]);

        return [
            'success' => true,
            'message' => __('general.otp_sent_successfully'),
            'masked_phones' => array_map([$this, 'maskPhone'], $phones),
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

    private function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return substr($phone, 0, 2) . str_repeat('*', max(0, $len - 4)) . substr($phone, -2);
    }
}
