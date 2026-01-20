<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingController extends Controller
{
    private SettingService $settingService;
    private $model = 'Setting';
    private $plural = 'settings';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(Request $request)
    {
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = $this->settingService->all();
        $title = __('general.edit');
        $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);

        $view = 'Admin.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(SettingRequest $request)
    {
        $newRequest = $request->only(
            'ar',
            'en',
            'language',
            'tips',
            'image',
        );
        $item = $this->settingService->edit($newRequest);
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('admin.settings.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
