<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Models\Center;
use App\Models\DeviceSession;
use App\Models\TrustedDevice;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;

class TrustedDeviceController extends Controller
{
    public function __construct(private TrustedDeviceService $devices)
    {
    }

    public function index($id)
    {
        $center = Center::withTrashed()->findOrFail($id);
        abort_unless($center->trusted_devices_enabled ?? false, 404);

        $devices = TrustedDevice::query()
            ->where('center_id', $center->id)
            ->orderByDesc('last_used_at')
            ->orderByDesc('id')
            ->get();

        $sessions = DeviceSession::query()
            ->with('device')
            ->where('center_id', $center->id)
            ->where('is_active', true)
            ->orderByDesc('started_at')
            ->get();

        $logs = AuthLog::query()
            ->where('center_id', $center->id)
            ->whereIn('action', ['login_success', 'login_failed'])
            ->orderByDesc('id')
            ->limit(150)
            ->get();

        $devicesByUuid = $devices->keyBy('device_uuid');

        $title = __('field.trusted_devices') . ' — ' . $center->name;
        $menu = __('locale.centers');
        $menu_link = route('admin.centers.index');

        return view('Admin.SubViews.Center.devices', compact(
            'center',
            'devices',
            'sessions',
            'logs',
            'devicesByUuid',
            'title',
            'menu',
            'menu_link'
        ));
    }

    public function destroy($id, $deviceId)
    {
        $center = Center::withTrashed()->findOrFail($id);
        $device = TrustedDevice::query()
            ->where('center_id', $center->id)
            ->where('id', $deviceId)
            ->firstOrFail();

        $this->devices->deleteDevice($device, 'Deleted by super admin');

        return back()->with('success', __('admin.operation_done_successfully'));
    }

    public function block($id, $deviceId)
    {
        $center = Center::withTrashed()->findOrFail($id);
        $device = TrustedDevice::query()
            ->where('center_id', $center->id)
            ->where('id', $deviceId)
            ->firstOrFail();

        $this->devices->blockDevice($device, 'Blocked by super admin');

        return back()->with('success', __('admin.operation_done_successfully'));
    }

    public function unblock($id, $deviceId)
    {
        $center = Center::withTrashed()->findOrFail($id);
        $device = TrustedDevice::query()
            ->where('center_id', $center->id)
            ->where('id', $deviceId)
            ->firstOrFail();

        $this->devices->unblockDevice($device);

        return back()->with('success', __('admin.operation_done_successfully'));
    }

    public function updateLimit(Request $request, $id)
    {
        $center = Center::withTrashed()->findOrFail($id);
        $request->validate([
            'center_device_limit' => 'nullable|integer|min:0|max:1000',
        ]);

        $center->update([
            'center_device_limit' => $request->filled('center_device_limit') ? (int) $request->input('center_device_limit') : null,
        ]);

        return back()->with('success', __('admin.operation_done_successfully'));
    }
}
