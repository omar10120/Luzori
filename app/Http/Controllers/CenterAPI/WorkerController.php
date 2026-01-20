<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Worker\WorkerRequest;
use App\Http\Requests\CenterAPI\Worker\CheckWorkerIdRequest;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\WorkerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkerController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Worker';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all(Request $request)
    {
        $relations = ['media', 'branch', 'shift', 'services' => function ($q) {
            $q->with(['service' => function ($q1) {
                $q1->whereNull('deleted_at');
            }]);
        }];

        if ($request->page == 0) {
            $items = $this->crudService->all($this->model, $relations, 0);
        } else {
            $items = $this->crudService->paginate($this->model, $relations, 0);
        }
        if ($items) {
            if ($request->page == 0) {
                $items = WorkerResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items);
            } else {
                $paginationData = PaginateDateResource::make($items);
                $items = WorkerResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckWorkerIdRequest $request)
    {
        $relations = ['media', 'branch', 'shift', 'services' => function ($q) {
            $q->with(['service' => function ($q1) {
                $q1->whereNull('deleted_at');
            }]);
        }];

        $item = $this->crudService->find($this->model, $request->id, $relations, 0);
        if ($item) {
            $item = WorkerResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(WorkerRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = WorkerResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(WorkerRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = WorkerResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckWorkerIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
