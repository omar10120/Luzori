<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InfoRequest;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InfoController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Info';
    private $plural = 'infos';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(Request $request)
    {
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = $this->crudService->find($this->model, 1, [], 0);
        $title = __('general.edit');
        $requestUrl = route($this->updateOrCreateRoute, ['id' => 1]);

        $view = 'Admin.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(InfoRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated());
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('admin.infos.index'));
            
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
