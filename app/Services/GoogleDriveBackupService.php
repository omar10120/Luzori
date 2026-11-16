<?php

namespace App\Services;

use App\Models\Center;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveBackupService
{
    private ?string $accessToken = null;

    public function clientId(): string
    {
        return (string) $this->setting('google_drive_client_id');
    }

    public function hasSecret(): bool
    {
        return $this->setting('google_drive_client_secret') !== '';
    }

    public function saveCredentials(string $clientId, ?string $clientSecret): void
    {
        $currentId = $this->clientId();
        $this->putSetting('google_drive_client_id', trim($clientId));
        if ($clientSecret !== null && trim($clientSecret) !== '') {
            $this->putSetting('google_drive_client_secret', trim($clientSecret));
        }
        if ($currentId !== '' && $currentId !== trim($clientId)) {
            $this->putSetting('google_drive_refresh_token', '');
            $this->putSetting('google_drive_root_folder_id', '');
        }
    }

    public function storeRefreshToken(string $refreshToken): void
    {
        $this->putSetting('google_drive_refresh_token', $refreshToken);
        $this->accessToken = null;
    }

    public function connected(): bool
    {
        try {
            return $this->accessToken() !== '';
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function authorizationUrl(string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function redirectUri(): string
    {
        return route('admin.settings.drive.callback');
    }

    public function exchangeCode(string $code): void
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->secret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful() || !$response->json('refresh_token')) {
            throw new RuntimeException($response->json('error_description') ?: 'Google did not return a refresh token.');
        }

        $this->storeRefreshToken((string) $response->json('refresh_token'));
    }

    public function backupCenters(): array
    {
        $results = [];
        $rootId = $this->rootFolderId();
        $keep = [
            Carbon::today()->toDateString(),
            Carbon::yesterday()->toDateString(),
            Carbon::today()->subDays(2)->toDateString(),
        ];

        Center::query()->whereNotNull('database')->orderBy('id')->each(function (Center $center) use (&$results, $rootId, $keep) {
            $database = (string) $center->database;
            if (preg_match('/^[A-Za-z0-9_-]+$/', $database) !== 1) {
                $results[] = $database . ' skipped';
                return;
            }

            $folderId = $this->folderId($this->folderName($center), $rootId);
            $date = Carbon::today()->toDateString();
            $fileName = $database . '_' . $date . '.sql';
            $path = storage_path('app/drive-backups/' . $fileName);

            try {
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }
                app(CenterDatabaseDumpService::class)->writeTo($path, $database, (string) $center->name);
                $this->deleteNamed($folderId, $fileName);
                $this->upload($path, $fileName, $folderId);
                $this->keepRecent($folderId, $keep);
                $results[] = $database . ' uploaded';
            } catch (\Throwable $e) {
                $results[] = $database . ' failed: ' . $e->getMessage();
            } finally {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        });

        return $results;
    }

    private function accessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $refresh = $this->setting('google_drive_refresh_token');
        if ($refresh === '' || $this->clientId() === '' || $this->secret() === '') {
            throw new RuntimeException('Google Drive is not connected.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId(),
            'client_secret' => $this->secret(),
            'refresh_token' => $refresh,
            'grant_type' => 'refresh_token',
        ]);

        $token = (string) $response->json('access_token');
        if (!$response->successful() || $token === '') {
            throw new RuntimeException($response->json('error_description') ?: 'Google token refresh failed.');
        }

        return $this->accessToken = $token;
    }

    private function rootFolderId(): string
    {
        $stored = $this->setting('google_drive_root_folder_id');
        if ($stored !== '' && $this->fileExists($stored)) {
            return $stored;
        }

        $id = $this->createFolder('Luzori Backups', null);
        $this->putSetting('google_drive_root_folder_id', $id);

        return $id;
    }

    private function folderId(string $name, string $parentId): string
    {
        $existing = $this->find($name, $parentId, 'application/vnd.google-apps.folder');
        if ($existing) {
            return $existing;
        }

        return $this->createFolder($name, $parentId);
    }

    private function createFolder(string $name, ?string $parentId): string
    {
        $body = [
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ];
        if ($parentId) {
            $body['parents'] = [$parentId];
        }

        $response = Http::withToken($this->accessToken())
            ->post('https://www.googleapis.com/drive/v3/files', $body);

        $id = (string) $response->json('id');
        if (!$response->successful() || $id === '') {
            throw new RuntimeException('Unable to create the Drive folder.');
        }

        return $id;
    }

    private function upload(string $path, string $name, string $parentId): void
    {
        $start = Http::withToken($this->accessToken())
            ->withHeaders([
                'X-Upload-Content-Type' => 'application/sql',
                'X-Upload-Content-Length' => (string) filesize($path),
                'Content-Type' => 'application/json; charset=UTF-8',
            ])
            ->send('POST', 'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable&fields=id', [
                'body' => json_encode([
                    'name' => $name,
                    'parents' => [$parentId],
                ]),
            ]);

        $location = $start->header('Location');
        if (!$start->successful() || !$location) {
            throw new RuntimeException('Drive upload did not start.');
        }

        $file = fopen($path, 'rb');
        $upload = Http::withHeaders(['Content-Type' => 'application/sql'])
            ->send('PUT', $location, ['body' => $file]);
        if (is_resource($file)) {
            fclose($file);
        }

        if (!$upload->successful()) {
            throw new RuntimeException('Drive upload failed.');
        }
    }

    private function keepRecent(string $folderId, array $keepDates): void
    {
        foreach ($this->children($folderId) as $file) {
            $name = (string) ($file['name'] ?? '');
            if (!preg_match('/_(\d{4}-\d{2}-\d{2})\.sql$/', $name, $match)) {
                $this->deleteFile((string) $file['id']);
                continue;
            }
            if (!in_array($match[1], $keepDates, true)) {
                $this->deleteFile((string) $file['id']);
            }
        }
    }

    private function deleteNamed(string $folderId, string $name): void
    {
        foreach ($this->children($folderId) as $file) {
            if (($file['name'] ?? '') === $name) {
                $this->deleteFile((string) $file['id']);
            }
        }
    }

    private function children(string $folderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->get('https://www.googleapis.com/drive/v3/files', [
                'q' => sprintf("'%s' in parents and trashed = false", $folderId),
                'fields' => 'files(id,name)',
                'pageSize' => 100,
            ]);

        return $response->json('files') ?: [];
    }

    private function find(string $name, string $parentId, string $mime): ?string
    {
        $query = sprintf(
            "name = '%s' and mimeType = '%s' and '%s' in parents and trashed = false",
            str_replace("'", "\\'", $name),
            $mime,
            $parentId
        );
        $response = Http::withToken($this->accessToken())
            ->get('https://www.googleapis.com/drive/v3/files', [
                'q' => $query,
                'fields' => 'files(id)',
                'pageSize' => 1,
            ]);

        return $response->json('files.0.id');
    }

    private function fileExists(string $id): bool
    {
        $response = Http::withToken($this->accessToken())
            ->get('https://www.googleapis.com/drive/v3/files/' . $id, [
                'fields' => 'id,trashed',
            ]);

        return $response->successful() && $response->json('trashed') !== true;
    }

    private function deleteFile(string $id): void
    {
        Http::withToken($this->accessToken())
            ->delete('https://www.googleapis.com/drive/v3/files/' . $id);
    }

    private function folderName(Center $center): string
    {
        $name = trim((string) ($center->name ?: $center->domain ?: $center->database));

        return $name !== '' ? $name : (string) $center->database;
    }

    private function secret(): string
    {
        return $this->setting('google_drive_client_secret');
    }

    private function setting(string $key): string
    {
        return (string) Setting::query()->where('key', $key)->value('value');
    }

    private function putSetting(string $key, string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
