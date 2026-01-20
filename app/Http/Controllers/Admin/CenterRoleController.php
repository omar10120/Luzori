<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\CenterRoleDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class CenterRoleController extends Controller
{
    private RoleService $roleService;
    private $model = 'CenterRole';
    private $plural = 'centerroles';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(CenterRoleDataTable $dataTable)
    {
        $title = __('locale.' . $this->plural);
        return $dataTable->render("Admin.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('admin')->user()->can($can)) {
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $permissions = Permission::where('guard_name', 'center')->get();
        $permissions = $permissions->groupBy('group');

        $item = null;
        if ($request->id) {
            $item = $this->roleService->find($request->id);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'Admin.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'permissions', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(RoleRequest $request)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
            $item = $this->roleService->edit($request->validated());
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
            $item = $this->roleService->add($request->validated());
        }
        if (!auth('admin')->user()->can($can)) {
            return abort(401);
        }

        if ($item) {
            return MyHelper::responseJSON(__('admin.operation_done_successfully'), $responseCode, $item);
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
