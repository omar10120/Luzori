<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSGatewayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $sender;

    public function __construct()
    {
        $this->baseUrl = config('services.sms_gateway.base_url', 'https://api-server14.com');
        $this->apiKey = config('services.sms_gateway.api_key', '');
        $this->sender = config('services.sms_gateway.sender', 'TEST');
    }

    public function sendSMS(string $mobile, string $message, int $language = 1): array
    {
        try {
            $response = Http::get($this->baseUrl . '/api/send.aspx', [
                'apikey' => $this->apiKey,
                'language' => $language,
                'sender' => $this->sender,
                'mobile' => $mobile,
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
                    'mobile' => $mobile,
                    'message' => $message,
                    'response' => $result,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS Gateway Exception', [
                'mobile' => $mobile,
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
