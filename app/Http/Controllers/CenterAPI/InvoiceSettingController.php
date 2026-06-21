<?php

namespace App\Http\Controllers\CenterAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceSettingsResource;
use App\Services\InvoiceSettingsService;
use Illuminate\Http\Response;

class InvoiceSettingController extends Controller
{
    public function all(InvoiceSettingsService $invoiceSettingsService)
    {
        $settings = $invoiceSettingsService->first();

        return MyHelper::responseJSON(
            __('api.settingExists'),
            Response::HTTP_OK,
            InvoiceSettingsResource::make($settings)
        );
    }
}
