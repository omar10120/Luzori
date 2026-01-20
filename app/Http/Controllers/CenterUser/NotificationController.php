<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\NotificationDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\NotificationRequest;
use App\Services\CRUDService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Notification';
    private $plural = 'notifications';
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

    public function index(NotificationDataTable $dataTable)
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

        $title = 'معلومات الأشعار';
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $relations = ['users'];
        $item = $this->crudService->find($this->model, $request->id, $relations, 0);

        $view = 'CenterUser.SubViews.' . $this->model . '.show';
        return view($view, compact('item', 'title', 'menu', 'menu_link'));
    }

    public function create(Request $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $users = $this->crudService->all('User', [], 0);

        $item = null;
        if ($request->id) {
            $relations = ['translation', 'users'];
            $item = $this->crudService->find($this->model, $request->id, $relations, 0);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'users', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(NotificationRequest $request, NotificationService $notificationService)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $item = $notificationService->add($request->all());
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.notifications.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
