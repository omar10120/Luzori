<?php

namespace App\Services;

use App\Models\AuthLog;
use App\Models\Center;
use App\Models\DeviceOtpCode;
use App\Models\DeviceSession;
use App\Models\TrustedDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TrustedDeviceService
{
    public function resolveDeviceUuid(Request $request, ?array $payload = null): string
    {
        $uuid = $request->input('device_uuid')
            ?: ($payload['device_uuid'] ?? null)
            ?: $request->cookie('luzori_device_uuid');

        if (!is_string($uuid) || trim($uuid) === '') {
            $uuid = (string) Str::uuid();
        }

        return substr(trim($uuid), 0, 255);
    }

    public function parseAgent(Request $request): array
    {
        $ua = (string) $request->userAgent();
        $browser = 'Unknown';
        $platform = 'Unknown';
        $type = 'desktop';

        if (stripos($ua, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'Chrome/') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Safari/') !== false && stripos($ua, 'Chrome/') === false) {
            $browser = 'Safari';
        } elseif (stripos($ua, 'Firefox/') !== false) {
            $browser = 'Firefox';
        }

        if (stripos($ua, 'Android') !== false) {
            $platform = 'Android';
            $type = 'mobile';
        } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
            $platform = 'iOS';
            $type = stripos($ua, 'iPad') !== false ? 'tablet' : 'mobile';
        } elseif (stripos($ua, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (stripos($ua, 'Mac OS') !== false || stripos($ua, 'Macintosh') !== false) {
            $platform = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $platform = 'Linux';
        }

        if (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
            $type = 'tablet';
        }

        return [
            'device_name' => trim($browser . ' on ' . $platform),
            'device_type' => $type,
            'platform' => $platform,
            'browser' => $browser,
            'user_agent' => $ua,
            'ip_address' => $request->ip(),
        ];
    }

    public function findDevice(int $centerId, string $deviceUuid): ?TrustedDevice
    {
        return TrustedDevice::query()
            ->where('center_id', $centerId)
            ->where('device_uuid', $deviceUuid)
            ->first();
    }

    public function trustedCount(int $centerId): int
    {
        return TrustedDevice::query()
            ->where('center_id', $centerId)
            ->where('is_blocked', false)
            ->count();
    }

    public function limitReached(Center $center, ?TrustedDevice $existing = null): bool
    {
        $limit = $center->center_device_limit;
        if ($limit === null || (int) $limit <= 0) {
            return false;
        }

        if ($existing && !$existing->is_blocked) {
            return false;
        }

        return $this->trustedCount((int) $center->id) >= (int) $limit;
    }

    public function log(
        int $centerId,
        string $action,
        ?int $userId = null,
        ?string $deviceUuid = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $notes = null
    ): void {
        AuthLog::query()->create([
            'center_id' => $centerId,
            'user_id' => $userId,
            'device_uuid' => $deviceUuid,
            'action' => $action,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    public function otpRecipients(Center $center): array
    {
        $emails = [];

        if (!empty($center->email) && filter_var($center->email, FILTER_VALIDATE_EMAIL)) {
            $emails[] = strtolower(trim((string) $center->email));
        }

        return array_values(array_unique($emails));
    }

    public function createAndSendOtp(Center $center, int $userId, string $userEmail, string $deviceUuid, Request $request): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DeviceOtpCode::query()->create([
            'center_id' => $center->id,
            'user_id' => $userId,
            'device_uuid' => $deviceUuid,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'used_at' => null,
            'created_at' => now(),
        ]);

        $recipients = $this->otpRecipients($center);
        $subject = 'Luzori device verification code';
        $body = "Staff email: {$userEmail}\nCenter: {$center->name}\nOTP: {$code}\nValid for 10 minutes\nTime: " . now()->toDateTimeString();

        foreach ($recipients as $to) {
            try {
                Mail::raw($body, function ($message) use ($to, $subject) {
                    $message->to($to)->subject($subject);
                });
            } catch (\Throwable $e) {
                \Log::error('Trusted device OTP email failed: ' . $e->getMessage());
            }
        }

        $this->log(
            (int) $center->id,
            'otp_sent',
            $userId,
            $deviceUuid,
            $request->ip(),
            $request->userAgent(),
            'Recipients: ' . implode(', ', $recipients)
        );

        return $code;
    }

    public function verifyOtp(Center $center, int $userId, string $deviceUuid, string $code): bool
    {
        $otp = DeviceOtpCode::query()
            ->where('center_id', $center->id)
            ->where('user_id', $userId)
            ->where('device_uuid', $deviceUuid)
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->orderByDesc('id')
            ->first();

        if (!$otp || !Hash::check(trim($code), $otp->code_hash)) {
            return false;
        }

        $otp->update(['used_at' => now()]);

        return true;
    }

    public function trustDevice(Center $center, int $userId, string $deviceUuid, Request $request): TrustedDevice
    {
        $meta = $this->parseAgent($request);
        $device = $this->findDevice((int) $center->id, $deviceUuid);

        if ($device) {
            $device->update([
                'user_id' => $userId,
                'device_name' => $meta['device_name'],
                'device_type' => $meta['device_type'],
                'platform' => $meta['platform'],
                'browser' => $meta['browser'],
                'ip_address' => $meta['ip_address'],
                'is_blocked' => false,
                'last_used_at' => now(),
            ]);
        } else {
            $device = TrustedDevice::query()->create([
                'center_id' => $center->id,
                'user_id' => $userId,
                'device_uuid' => $deviceUuid,
                'device_name' => $meta['device_name'],
                'device_type' => $meta['device_type'],
                'platform' => $meta['platform'],
                'browser' => $meta['browser'],
                'ip_address' => $meta['ip_address'],
                'is_blocked' => false,
                'last_used_at' => now(),
            ]);
        }

        $this->log((int) $center->id, 'device_trusted', $userId, $deviceUuid, $request->ip(), $request->userAgent());

        return $device->fresh();
    }

    public function startSession(TrustedDevice $device, Request $request): DeviceSession
    {
        DeviceSession::query()
            ->where('device_id', $device->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);

        $device->update(['last_used_at' => now()]);

        return DeviceSession::query()->create([
            'center_id' => $device->center_id,
            'user_id' => $device->user_id,
            'device_id' => $device->id,
            'ip_address' => $request->ip(),
            'started_at' => now(),
            'ended_at' => null,
            'is_active' => true,
        ]);
    }

    public function endActiveSessionsForDevice(int $deviceId): void
    {
        DeviceSession::query()
            ->where('device_id', $deviceId)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);
    }

    public function endActiveSessionsForUser(int $centerId, int $userId, ?string $deviceUuid = null): void
    {
        $query = DeviceSession::query()
            ->where('center_id', $centerId)
            ->where('user_id', $userId)
            ->where('is_active', true);

        if ($deviceUuid) {
            $deviceIds = TrustedDevice::query()
                ->where('center_id', $centerId)
                ->where('device_uuid', $deviceUuid)
                ->pluck('id');
            $query->whereIn('device_id', $deviceIds);
        }

        $query->update([
            'is_active' => false,
            'ended_at' => now(),
        ]);
    }

    public function deleteDevice(TrustedDevice $device, ?string $notes = null): void
    {
        $this->endActiveSessionsForDevice((int) $device->id);
        $this->log(
            (int) $device->center_id,
            'device_removed',
            (int) $device->user_id,
            $device->device_uuid,
            request()->ip(),
            request()->userAgent(),
            $notes
        );
        $device->delete();
    }

    public function blockDevice(TrustedDevice $device, ?string $notes = null): void
    {
        $device->update(['is_blocked' => true]);
        $this->endActiveSessionsForDevice((int) $device->id);
        $this->log(
            (int) $device->center_id,
            'device_blocked',
            (int) $device->user_id,
            $device->device_uuid,
            request()->ip(),
            request()->userAgent(),
            $notes
        );
    }
}
