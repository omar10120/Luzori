<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\WorkerDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Services\NotifiesCenter;
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
    use NotifiesCenter;

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
        return $dataTable->render('CenterUser.SubViews.Worker.list', compact('title'));
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

        $user = auth('center_user')->user();
        $branchId = $user->branch_id ?? null;

        if ($branchId === null) {
            $branches = Branch::with(['translation'])->get();
        } else {
            if (get_user_role() != 1) {
                $branches = Branch::with(['translation'])->where('id', $branchId)->get();
            } else {
                $branches = Branch::with(['translation'])->get();
            }
        }
        $services = Service::with(['translation'])->get();
        $shifts = Shift::all();
        $canManagePercentage = (int) get_user_role() === 1;

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'branches', 'services', 'shifts', 'requestUrl', 'title', 'menu', 'menu_link', 'canManagePercentage'));
    }

    public function updateOrCreate(WorkerRequest $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $data = $request->validated();
        if ((int) get_user_role() !== 1) {
            unset($data['percentage']);
        }

        $item = $this->crudService->updateOrCreate($this->model, $data, true);
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.workers.index'));
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Delete a worker and notify the center.
     */
    public function destroy(Request $request)
    {
        $can = 'DELETE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $worker = Worker::findOrFail($request->id);

        $nameEn = $worker->translate('en')?->name ?: ($worker->name ?? '');
        $nameAr = $worker->translate('ar')?->name ?: ($worker->name ?? '');

        // If your CRUDService exposes delete, swap this line for it.
        // e.g. $this->crudService->delete($this->model, $worker->id);
        $worker->delete();

        $this->sendWorkerDeletedNotification($worker->id, $nameEn, $nameAr);

        return MyHelper::responseJSON('redirect_to_home', Response::HTTP_OK, route('center_user.workers.index'));
    }

    public function info(Request $request)
    {
        return Worker::select('id', 'name', 'phone', 'is_center_user')->find($request->worker_id);
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
        $centerUser = auth('center_api')->user() ?? auth('center_user')->user();
        $isAdmin    = ((int) get_user_role() === 1);

        $query = Worker::query();

        // Admins / super-admins: no branch filter → return workers from all branches
        if (! $isAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $workers = $query
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'is_center_user', 'branch_id']);

        return response()->json($workers);
    }

    /**
     * Notify the active center that a worker has been deleted.
     */
    private function sendWorkerDeletedNotification(int $workerId, string $nameEn, string $nameAr): void
    {
        $this->notifyCenter('system', [
            'en' => [
                'title' => 'Worker deleted',
                'text'  => 'Worker "' . $nameEn . '" has been deleted.',
            ],
            'ar' => [
                'title' => 'تم حذف الموظف',
                'text'  => 'تم حذف الموظف "' . $nameAr . '".',
            ],
        ], [
            'worker_id' => (string) $workerId,
        ]);
    }
}