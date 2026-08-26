<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Services\AppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class InboxNotificationController extends Controller
{
    protected function currentCenter(): ?Center
    {
        $currentDb = Config::get('database.connections.mysql.database');
        return Center::where('database', $currentDb)->first()
            ?? (session('active_center_domain')
                ? Center::where('domain', session('active_center_domain'))->first()
                : null);
    }

    public function inbox(Request $request, AppNotificationService $service)
    {
        if (!auth('center_user')->user()->can('VIEW_NOTIFICATIONS', 'center_api')) {
            return abort(403);
        }

        $center = $this->currentCenter();
        if (!$center) {
            return abort(404);
        }

        $title = __('field.received_notifications');
        $menu = __('locale.notifications');
        $menu_link = route('center_user.notifications.inbox');

        $notifications = $service->getForCenter($center, 20);

        return view('CenterUser.SubViews.Notification.inbox', compact(
            'title',
            'menu',
            'menu_link',
            'notifications',
            'center'
        ));
    }

    public function markSeen(Request $request, AppNotificationService $service)
    {
        if (!auth('center_user')->user()->can('VIEW_NOTIFICATIONS', 'center_api')) {
            return abort(403);
        }

        $center = $this->currentCenter();
        if (!$center) {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_NOT_FOUND);
        }

        $service->markSeenForCenter($center, $request->id ? (int) $request->id : null);

        return MyHelper::responseJSON(__('api.notificationSeen'), Response::HTTP_OK);
    }
}
