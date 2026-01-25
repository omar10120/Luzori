<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\CenterUserDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\CenterUserRequest;
use App\Models\Branch;
use App\Services\CenterUserService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CenterUserController extends Controller
{
    private CRUDService $crudService;
    private $model = 'CenterUser';
    private $plural = 'centerusers';
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

    public function index(CenterUserDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }
        
        $title = __('locale.' . $this->plural);
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $relations = ['media'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        $branches = Branch::with(['translation'])->get();
        $roles = Role::where('guard_name', 'center_api')->get();
        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'roles', 'branches', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(CenterUserRequest $request, CenterUserService $centerUserService)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        if (isset($request->id)) {
            $item = $centerUserService->edit($request->validated());
        } else {
            $item = $centerUserService->add($request->validated());
        }

        if ($item) {
            // return MyHelper::responseJSON(__('admin.operation_done_successfully'), $responseCode, $item);
            // Redirect back to the center users index page on success
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route($this->indexRoute));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeStatusWeb(Request $request, CenterUserService $centerUserService)
    {
        $can = 'DELETE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $item = $centerUserService->changeStatusWeb($request->id);
        if ($item) {
            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
