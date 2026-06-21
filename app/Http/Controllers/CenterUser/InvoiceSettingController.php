<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceSettingRequest;
use App\Services\InvoiceSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class InvoiceSettingController extends Controller
{
    private InvoiceSettingsService $invoiceSettingsService;
    private string $model = 'InvoiceSettings';
    private string $plural = 'invoice_settings';
    private string $indexRoute;
    private string $updateOrCreateRoute;

    public function __construct(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $item = $this->invoiceSettingsService->first();
        $title = __('general.edit');
        $requestUrl = route($this->updateOrCreateRoute);

        $view = 'CenterUser.SubViews.' . $this->model . '.index';

        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(InvoiceSettingRequest $request)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $item = $this->invoiceSettingsService->update($request->validated());

        if ($item) {
            return MyHelper::responseJSON(
                'redirect_to_home',
                Response::HTTP_CREATED,
                route('center_user.invoice_settings.index')
            );
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
