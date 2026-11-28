<?php

namespace App\Http\Middleware;

use App\Models\Center;
use App\Services\TrustedDeviceService;
use Closure;
use Illuminate\Http\Request;

class CheckTrustedDevice
{
    public function __construct(private TrustedDeviceService $devices)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (!auth('center_user')->check()) {
            return $next($request);
        }

        $centerId = session('trusted_center_id');
        $center = $centerId
            ? Center::query()->find($centerId)
            : $this->resolveCenterFromSession();

        if (!$center || !($center->trusted_devices_enabled ?? false)) {
            return $next($request);
        }

        $deviceUuid = session('trusted_device_uuid') ?: $request->cookie('luzori_device_uuid');
        if (!$deviceUuid) {
            return $next($request);
        }

        $device = $this->devices->findDevice((int) $center->id, (string) $deviceUuid);
        if (!$device) {
            auth('center_user')->logout();
            session()->forget(['trusted_device_uuid', 'trusted_center_id', 'trusted_device_id', 'active_center_domain']);

            return redirect()->route('center_user.login.view')
                ->with('error', __('field.device_removed_login_again'));
        }

        if ($device->is_blocked) {
            auth('center_user')->logout();
            session()->forget(['trusted_device_uuid', 'trusted_center_id', 'trusted_device_id', 'active_center_domain']);

            return redirect()->route('center_user.login.view')
                ->with('error', __('field.device_blocked_login'));
        }

        $device->update(['last_used_at' => now()]);

        return $next($request);
    }

    private function resolveCenterFromSession(): ?Center
    {
        $domain = session('active_center_domain');
        if ($domain) {
            return Center::query()->where('domain', $domain)->first();
        }

        return null;
    }
}
