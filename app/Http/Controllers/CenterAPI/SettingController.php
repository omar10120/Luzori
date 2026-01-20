<?php

namespace App\Http\Controllers\CenterAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Illuminate\Http\Response;

class SettingController extends Controller
{
    public function all(SettingService $settingService)
    {
        $settings = $settingService->all();
        if ($settings) {
            $settings = SettingResource::collection($settings);
            return MyHelper::responseJSON(__('api.settingExists'), Response::HTTP_OK, $settings);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
