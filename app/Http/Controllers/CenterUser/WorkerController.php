<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\WorkerDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\WorkerRequest;
use App\Services\CRUDService;
use App\Services\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Worker;

class WorkerController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Worker';
    private $plural = 'workers';
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

    public function index(WorkerDataTable $dataTable)
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
            $relations = ['services'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $branches = Branch::with(['translation'])->get();
        $services = Service::with(['translation'])->get();
        $shifts = Shift::all();

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'branches', 'services', 'shifts', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(WorkerRequest $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.workers.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function info(Request $request)
    {
        return Worker::select('name')->find($request->worker_id);
    }

    public function getWorkersByService(Request $request, WorkerService $workerService)
    {
        $branch_id = null;
        if (auth('center_user')->check()) {
            $branch_id = auth('center_user')->user()->branch_id;
        }
        $workers = $workerService->getByService($request->service_id, $branch_id);
        return response()->json($workers);
    }

    public function getWorkersByBranch(Request $request)
    {
        $branch_id = $request->branch_id;
        if (!$branch_id && auth('center_user')->check()) {
            $branch_id = auth('center_user')->user()->branch_id;
        }
        $workers = Worker::when($branch_id, function($query) use ($branch_id) {
            return $query->where('branch_id', $branch_id);
        })->select('id', 'name')->get();
        return response()->json($workers);
    }
}
