<?php

namespace App\Services;

use App\Models\FirebaseSetting;

class FirebaseSettingService
{
    public function get(): FirebaseSetting
    {
        return FirebaseSetting::current() ?? new FirebaseSetting();
    }

    public function save(array $data): FirebaseSetting
    {
        $setting = FirebaseSetting::current() ?? new FirebaseSetting();

        $setting->fill([
            'service_account_json' => $data['service_account_json'] ?? $setting->service_account_json,
            'api_key' => $data['api_key'] ?? null,
            'auth_domain' => $data['auth_domain'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'storage_bucket' => $data['storage_bucket'] ?? null,
            'messaging_sender_id' => $data['messaging_sender_id'] ?? null,
            'app_id' => $data['app_id'] ?? null,
            'measurement_id' => $data['measurement_id'] ?? null,
        ]);
        $setting->save();

        $this->syncServiceAccountFile($setting);

        return $setting;
    }

    protected function syncServiceAccountFile(FirebaseSetting $setting): void
    {
        $json = $setting->service_account_json;
        if (empty($json)) {
            return;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return;
        }

        $dir = storage_path('firebase');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/service-account.json';
        file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $configured = config('firebase.credentials.file');
        if ($configured && is_string($configured)) {
            $configDir = dirname($configured);
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }
            if (!file_exists($configured) || basename($configured) === 'service-account.json') {
                file_put_contents($configured, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        }
    }
}
