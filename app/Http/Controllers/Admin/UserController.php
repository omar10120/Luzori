<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\UserDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppUserRequest;
use App\Services\AppUserService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    private CRUDService $crudService;
    private $model = 'AppUser';
    private $plural = 'users';
    private $indexRoute;
    private $updateOrCreateRoute;

    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(UserDataTable $dataTable)
    {
        $title = "App Users";
        return $dataTable->render("Admin.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        $can = isset($request->id) ? 'UPDATE_USERS' : 'CREATE_USERS';
        if (!auth('admin')->user()->can($can)) {
            return abort(401);
        }

        $menu = "App Users";
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $relations = ['media'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'Admin.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(AppUserRequest $request, AppUserService $userService)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_USERS';
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_USERS';
        }

        if (!auth('admin')->user()->can($can)) {
            return abort(401);
        }

        if (isset($request->id)) {
            $item = $userService->edit($request->validated());
        } else {
            $item = $userService->add($request->validated());
        }

        if ($item) {
            return MyHelper::responseJSON(__('admin.operation_done_successfully'), $responseCode, $item);
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
