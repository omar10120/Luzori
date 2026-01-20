<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Helpers\MyHelper;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function get(NotificationService $notificationService)
    {
        $notifications = $notificationService->getByCenterUser(auth('center_api')->user()->id);
        if ($notifications || empty($notifications)) {
            return MyHelper::responseJSON(__('api.notificationExists'), Response::HTTP_OK, $notifications);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
