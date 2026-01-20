<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\UserDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\UserRequest;
use App\Services\UserService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private CRUDService $crudService;
    private $model = 'User';
    private $plural = 'users';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(UserDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }
        
        $title = __('locale.' . $this->plural);
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
    }

    public function show(Request $request)
    {
        $can = 'SHOW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $title = 'معلومات المستخدم';
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $relations = ['media'];
        $item = $this->crudService->find($this->model, $request->id, $relations, 1);

        $view = 'CenterUser.SubViews.' . $this->model . '.show';
        return view($view, compact('item', 'title', 'menu', 'menu_link'));
    }

    public function create(Request $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
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

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(UserRequest $request, UserService $userService)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        if (isset($request->id)) {
            $item = $userService->edit($request->validated());
        } else {
            $item = $userService->add($request->validated());
        }

        if ($item) {
            if ($request->ajax()) {
                return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, $item);
            }
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.users.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
