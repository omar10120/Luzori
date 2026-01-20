<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\AdminDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Services\AdminService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Admin';
    private $plural = 'admins';
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

    public function index(AdminDataTable $dataTable)
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

        $item = null;
        if ($request->id) {
            $relations = ['media'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        $roles = Role::where('guard_name', 'admin')->get();
        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'Admin.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'roles', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(AdminRequest $request, AdminService $adminService)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
            $item = $adminService->edit($request->validated());
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
            $item = $adminService->add($request->validated());
        }
        if (!auth('admin')->user()->can($can)) {
            return abort(401);
        }

        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('admin.admins.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
