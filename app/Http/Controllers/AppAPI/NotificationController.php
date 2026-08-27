<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Services\AppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function get(Request $request, AppNotificationService $service)
    {
        if ($request->filled('locale') && in_array($request->locale, ['ar', 'en'], true)) {
            app()->setLocale($request->locale);
        }

        $user = $request->user();
        $notifications = $service->getForAppUser($user, (int) $request->get('per_page', 20));

        $data = collect($notifications->items())->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'text' => $n->text,
                'image' => $n->image_url,
                'type' => $n->type,
                'is_read' => (bool) ($n->pivot->is_read ?? false),
                'created_at' => $n->created_at,
            ];
        });

        return MyHelper::responseJSON(__('api.notificationsFetched'), Response::HTTP_OK, [
            'notifications' => $data,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function seen(Request $request, AppNotificationService $service)
    {
        $request->validate([
            'notification_id' => 'nullable|integer',
        ]);

        $service->markSeen($request->user(), $request->notification_id ? (int) $request->notification_id : null);

        return MyHelper::responseJSON(__('api.notificationSeen'), Response::HTTP_OK);
    }
}
