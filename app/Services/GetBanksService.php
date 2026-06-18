<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GetBanksService
{
    public function fetch(): array
    {
        $token   = env('MYFATOORAH_TOKEN');
        $baseUrl = rtrim((string) env('MYFATOORAH_BASE_URL'), '/');

        if (empty($token) || empty($baseUrl)) {
            throw new RuntimeException('MyFatoorah credentials are not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ])->get($baseUrl . '/v2/GetBanks');

        if (!$response->successful()) {
            Log::error('GetBanksService HTTP error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException($response->json('Message') ?: 'Failed to fetch banks from MyFatoorah.');
        }

        $body = $response->json();

        if (!is_array($body)) {
            return [];
        }

        // MyFatoorah standard envelope: { IsSuccess, Message, Data: [...] }
        if (array_key_exists('IsSuccess', $body)) {
            if ($body['IsSuccess'] !== true) {
                throw new RuntimeException($body['Message'] ?? 'Failed to fetch banks from MyFatoorah.');
            }

            return $body['Data'] ?? [];
        }

        // Direct list response: [{ Value, Text }, ...]
        return $body;
    }
}
