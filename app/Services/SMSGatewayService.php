<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSGatewayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $sender;
    private string $adminPhones;
    public function __construct()
    {
        $this->baseUrl = config('services.sms_gateway.base_url', 'https://api-server14.com');
        $this->apiKey = config('services.sms_gateway.api_key', '');
        $this->sender = config('services.sms_gateway.sender', 'TEST');
        $this->adminPhones = config('services.sms_gateway.admin_phones', '');
    }

    public function formatPhoneNumber(string $mobile): string
    {
        $mobile = trim($mobile);
        $mobile = str_replace('+', '', $mobile);
        return $mobile;
    }

    public function sendSMS(string $mobile, string $message, int $language = 1): array
    {
        try {
            $formattedMobile = $this->formatPhoneNumber($mobile);
            
            $response = Http::get($this->baseUrl . '/api/send.aspx', [
                'apikey' => $this->apiKey,
                'language' => $language,
                'sender' => $this->sender,
                'mobile' => $formattedMobile,
                'message' => $message,
            ]);

            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'body' => $response->body(),
                'data' => $response->json(),
            ];

            if (!$response->successful()) {
                Log::error('SMS Gateway Error', [
                    'mobile' => $formattedMobile,
                    'original_mobile' => $mobile,
                    'message' => $message,
                    'response' => $result,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS Gateway Exception', [
                'mobile' => $this->formatPhoneNumber($mobile),
                'original_mobile' => $mobile,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendSMSMultiple(array $mobiles, string $message, int $language = 1): array
    {
        $mobileString = implode(',', $mobiles);
        return $this->sendSMS($mobileString, $message, $language);
    }

    public function sendEnglishSMS(string $mobile, string $message): array
    {
        return $this->sendSMS($mobile, $message, 1);
    }

    public function sendArabicSMS(string $mobile, string $message): array
    {
        return $this->sendSMS($mobile, $message, 2);
    }

    public function sendUnicodeSMS(string $mobile, string $message): array
    {
        return $this->sendSMS($mobile, $message, 3);
    }

    public function sendSMSWithTemplate(string $mobile, string $template, array $replacements, string $locale = 'en'): array
    {
        $message = $template;
        foreach ($replacements as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        $formattedMobile = $this->formatPhoneNumber($mobile);
        $language = $locale === 'ar' ? 2 : 1;
        return $this->sendSMS($formattedMobile, $message, $language);
    }

    public function checkBalance(): array
    {
        try {
            $response = Http::get($this->baseUrl . '/api/balance.aspx', [
                'apikey' => $this->apiKey,
            ]);

            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'body' => $response->body(),
                'data' => $response->json(),
            ];

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS Gateway Balance Check Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
