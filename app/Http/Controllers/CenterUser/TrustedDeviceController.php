<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Models\Center;
use App\Models\CenterUser;
use App\Models\DeviceSession;
use App\Models\TrustedDevice;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class TrustedDeviceController extends Controller
{
    public function __construct(private TrustedDeviceService $devices)
    {
    }

    public function index()
    {
        $center = $this->currentCenter();
        abort_unless($center, 404);

        $devices = TrustedDevice::query()
            ->where('center_id', $center->id)
            ->orderByDesc('last_used_at')
            ->orderByDesc('id')
            ->get();

        $pendingOtps = \App\Models\DeviceOtpCode::query()
            ->where('center_id', $center->id)
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->orderByDesc('id')
            ->get()
            ->unique(fn ($row) => $row->device_uuid . ':' . $row->user_id)
            ->values();

        $userNames = $this->userNames($devices->pluck('user_id')->merge($pendingOtps->pluck('user_id'))->unique()->all());

        $sessions = DeviceSession::query()
            ->with('device')
            ->where('center_id', $center->id)
            ->where('is_active', true)
            ->orderByDesc('started_at')
            ->get();

        $logs = AuthLog::query()
            ->where('center_id', $center->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $title = __('field.trusted_devices');
        $menu = __('locale.mainsettings');
        $menu_link = route('center_user.mainsettings.index');

        return view('CenterUser.SubViews.TrustedDevice.index', compact(
            'center',
            'devices',
            'pendingOtps',
            'userNames',
            'sessions',
            'logs',
            'title',
            'menu',
            'menu_link'
        ));
    }

    public function destroy($id)
    {
        $center = $this->currentCenter();
        abort_unless($center, 404);

        $device = TrustedDevice::query()
            ->where('center_id', $center->id)
            ->where('id', $id)
            ->firstOrFail();

        $this->devices->deleteDevice($device, 'Deleted from center settings');

        return back()->with('success', __('admin.operation_done_successfully'));
    }

    public function block($id)
    {
        $center = $this->currentCenter();
        abort_unless($center, 404);

        $device = TrustedDevice::query()
            ->where('center_id', $center->id)
            ->where('id', $id)
            ->firstOrFail();

        $this->devices->blockDevice($device, 'Blocked from center settings');

        return back()->with('success', __('admin.operation_done_successfully'));
    }

    public function updateExtraEmails(Request $request)
    {
        $center = $this->currentCenter();
        abort_unless($center, 404);

        $request->validate([
            'device_otp_extra_emails' => 'nullable|string|max:2000',
        ]);

        $center->update([
            'device_otp_extra_emails' => $request->input('device_otp_extra_emails'),
        ]);

        return back()->with('success', __('admin.operation_done_successfully'));
    }

    private function currentCenter(): ?Center
    {
        $domain = session('active_center_domain');
        if ($domain) {
            return Center::where('domain', $domain)->first();
        }

        $dbName = Config::get('database.connections.mysql.database');

        return Center::where('database', $dbName)->first();
    }

    private function userNames(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        return CenterUser::withTrashed()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'email'])
            ->mapWithKeys(fn ($user) => [$user->id => ($user->name ?: $user->email)])
            ->all();
    }
}
