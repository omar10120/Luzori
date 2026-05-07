<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\GlobalCategoryDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GlobalCategoryRequest;
use App\Services\GlobalCategoryService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class GlobalCategoryController extends Controller
{
    private GlobalCategoryService $globalCategoryService;
    private CRUDService $crudService;
    private $model = 'GlobalCategory';
    private $plural = 'global-categories';
    private $indexRoute;
    private $updateOrCreateRoute;

    public function __construct(GlobalCategoryService $globalCategoryService, CRUDService $crudService)
    {
        $this->globalCategoryService = $globalCategoryService;
        $this->crudService = $crudService;
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(GlobalCategoryDataTable $dataTable)
    {
        $title = __('locale.' . $this->plural);
        return $dataTable->render("Admin.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $item = $this->crudService->find($this->model, $request->id);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'Admin.SubViews.GlobalCategories.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(GlobalCategoryRequest $request)
    {
        $item = $this->globalCategoryService->store($request->validated());

        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('admin.global-categories.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
