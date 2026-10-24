<?php

namespace App\Services;

use App\Models\FirebaseSetting;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class FirebaseRealtimeService
{
    public function databaseUrl(?FirebaseSetting $setting = null): ?string
    {
        $setting = $setting ?: FirebaseSetting::current();
        if (!$setting || empty($setting->project_id)) {
            return null;
        }

        // Default RTDB URL pattern; override via env if region differs
        return config('firebase.database_url')
            ?: ('https://' . $setting->project_id . '-default-rtdb.firebaseio.com');
    }

    public function webConfig(): ?array
    {
        $setting = FirebaseSetting::current();
        if (!$setting || empty($setting->api_key) || empty($setting->project_id)) {
            return null;
        }

        return [
            'apiKey' => $setting->api_key,
            'authDomain' => $setting->auth_domain,
            'projectId' => $setting->project_id,
            'storageBucket' => $setting->storage_bucket,
            'messagingSenderId' => $setting->messaging_sender_id,
            'appId' => $setting->app_id,
            'measurementId' => $setting->measurement_id,
            'databaseURL' => $this->databaseUrl($setting),
        ];
    }

    public function pushSupportMessage(int $centerId, array $payload): ?string
    {
        try {
            $db = $this->database();
            if (!$db) {
                return null;
            }

            $ref = $db->getReference('support_chats/' . $centerId . '/messages')->push($payload);
            $db->getReference('support_chats/' . $centerId . '/meta')->update([
                'last_message' => $payload['body'] ?? '',
                'last_sender_name' => $payload['sender_name'] ?? '',
                'last_sender_type' => $payload['sender_type'] ?? '',
                'last_message_at' => $payload['created_at'] ?? now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ]);

            return $ref->getKey();
        } catch (\Throwable $e) {
            Log::warning('Firebase RTDB push failed: ' . $e->getMessage());
            return null;
        }
    }

    public function createCustomToken(string $uid, array $claims = []): ?string
    {
        try {
            $auth = $this->auth();
            if (!$auth) {
                return null;
            }

            return $auth->createCustomToken($uid, $claims)->toString();
        } catch (\Throwable $e) {
            Log::warning('Firebase custom token failed: ' . $e->getMessage());
            return null;
        }
    }

    public function setTyping(int $centerId, array $payload): void
    {
        try {
            $db = $this->database();
            if (!$db) {
                return;
            }
            $key = ($payload['sender_type'] ?? 'user') . '_' . ($payload['sender_id'] ?? '0');
            $db->getReference('support_chats/' . $centerId . '/typing/' . $key)->set([
                'sender_type' => $payload['sender_type'] ?? null,
                'sender_id' => $payload['sender_id'] ?? null,
                'sender_name' => $payload['sender_name'] ?? null,
                'at' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Firebase typing set failed: ' . $e->getMessage());
        }
    }

    public function clearTyping(int $centerId, string $key): void
    {
        try {
            $db = $this->database();
            if (!$db) {
                return;
            }
            $db->getReference('support_chats/' . $centerId . '/typing/' . $key)->remove();
        } catch (\Throwable $e) {
            Log::warning('Firebase typing clear failed: ' . $e->getMessage());
        }
    }

    public function pushSeen(int $centerId, array $payload): void
    {
        try {
            $db = $this->database();
            if (!$db) {
                return;
            }
            $db->getReference('support_chats/' . $centerId . '/seen')->push($payload);
        } catch (\Throwable $e) {
            Log::warning('Firebase seen push failed: ' . $e->getMessage());
        }
    }


    protected function factory(): ?Factory
    {
        $setting = FirebaseSetting::current();
        $serviceAccount = $setting?->serviceAccountArray();

        if ($serviceAccount) {
            $factory = (new Factory)->withServiceAccount($serviceAccount);
            $url = $this->databaseUrl($setting);
            if ($url) {
                $factory = $factory->withDatabaseUri($url);
            }
            return $factory;
        }

        $file = config('firebase.credentials.file');
        if ($file && is_file($file)) {
            $factory = (new Factory)->withServiceAccount($file);
            $url = $this->databaseUrl($setting);
            if ($url) {
                $factory = $factory->withDatabaseUri($url);
            }
            return $factory;
        }

        return null;
    }

    protected function database()
    {
        $factory = $this->factory();
        return $factory ? $factory->createDatabase() : null;
    }

    protected function auth()
    {
        $factory = $this->factory();
        return $factory ? $factory->createAuth() : null;
    }
}
