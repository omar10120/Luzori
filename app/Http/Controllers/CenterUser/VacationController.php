<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\VacationRequest;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Worker;
use Illuminate\Support\Str;

class VacationController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Vacation';
    private $plural = 'vacations';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.workers.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function create(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.add_vacation_to');
        $menu = __('locale.workers');
        $menu_link = route($this->indexRoute);

        $item = Worker::with(['vacations' => function ($item) {
            $item->with('worker');
        }])->find($request->id);

        $requestUrl = route($this->updateOrCreateRoute);
        $view = 'CenterUser.SubViews.Worker.vacation';
        return view($view, compact('requestUrl', 'item', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(VacationRequest $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $item = $this->crudService->updateOrCreate($this->model, $request->validated());
        if ($item) {
            $route = route('center_user.'. $this->plural .'.create', ['id' => $request->worker_id]);
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, $route);
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
