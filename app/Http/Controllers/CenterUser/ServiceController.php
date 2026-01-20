<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\ServiceDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\ServiceRequest;
use App\Models\Service;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Service';
    private $plural = 'services';
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

    public function index(ServiceDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
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
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $relations = ['media', 'translation'];
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

    public function updateOrCreate(ServiceRequest $request)
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

        $newRequest = $request->only(
            'id',
            'ar',
            'en',
            'rooms_no',
            'free_book',
            'price',
            'has_commission',
            'is_top',
            'image',
            
        );

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            // If quick add request, return service data
            if ($request->has('quick_add') && $request->quick_add == '1') {
                $item->load('translation');
                return response()->json([
                    'message' => __('admin.operation_done_successfully'),
                    'service' => [
                        'id' => $item->id,
                        'name' => $item->translate(app()->getLocale())->name ?? $item->name,
                        'price' => $item->price ?? 0,
                        'has_commission' => $item->has_commission ?? false
                    ]
                ], Response::HTTP_CREATED);
            }
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.services.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function info(Request $request)
    {
        return Service::select('price')->find($request->service_id);
    }
}
