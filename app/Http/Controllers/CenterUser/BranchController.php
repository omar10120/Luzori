<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\BranchDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\BranchRequest;
use App\Services\CRUDService;
use App\Services\NotifiesCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    use NotifiesCenter;

    private CRUDService $crudService;
    private $model = 'Branch';
    private $plural = 'branches';
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

    public function index(BranchDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render("CenterUser.SubViews.Branch.list", compact('title'));
    }

    public function create(Request $request)
    {
        $can = isset($request->id)
            ? 'UPDATE_' . Str::upper($this->plural)
            : 'CREATE_' . Str::upper($this->plural);

        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $item = $this->crudService->find($this->model, $request->id, ['translation']);
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

    public function updateOrCreate(BranchRequest $request)
    {
        $can = isset($request->id)
            ? 'UPDATE_' . Str::upper($this->plural)
            : 'CREATE_' . Str::upper($this->plural);

        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $newRequest = $request->only(
            'id',
            'ar',
            'en',
            'longitude',
            'latitude',
            'open_time',
            'close_time',
        );

        $isCreate = empty($request->id);
        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);

        if ($item) {
            if ($isCreate) {
                $this->sendNewBranchWelcome($item);
            }
            return MyHelper::responseJSON(
                'redirect_to_home',
                Response::HTTP_CREATED,
                route('center_user.branches.index')
            );
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function sendNewBranchWelcome($branch): void
    {
        $nameEn = $branch->translate('en')?->name ?: ($branch->name ?? '');
        $nameAr = $branch->translate('ar')?->name ?: ($branch->name ?? '');

        $this->notifyCenter('system', [
            'en' => [
                'title' => 'Welcome to the new branch',
                'text'  => 'Welcome! A new branch "' . $nameEn . '" has been added.',
            ],
            'ar' => [
                'title' => 'مرحباً بالفرع الجديد',
                'text'  => 'مرحباً! تم إضافة فرع جديد "' . $nameAr . '".',
            ],
        ], [
            'branch_id' => (string) $branch->id,
        ]);
    }
}