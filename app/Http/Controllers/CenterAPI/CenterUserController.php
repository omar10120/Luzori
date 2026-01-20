<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\CenterUser\CenterUserRequest;
use App\Http\Requests\CenterAPI\CenterUser\CheckCenterUserIdRequest;
use App\Http\Resources\CenterUserResource;
use App\Http\Resources\PaginateDateResource;
use Illuminate\Http\Response;

class CenterUserController extends Controller
{
    private CRUDService $crudService;
    private $model = 'CenterUser';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all()
    {
        $relation = ['media', 'roles' => function ($q) {
            $q->with(['permissions']);
        }];
        $items = $this->crudService->paginate($this->model, $relation, 0);
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = CenterUserResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckCenterUserIdRequest $request)
    {
        $relation = ['media', 'roles' => function ($q) {
            $q->with(['permissions']);
        }];
        $item = $this->crudService->find($this->model, $request->id, $relation, 0);
        if ($item) {
            $item = CenterUserResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(CenterUserRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = CenterUserResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(CenterUserRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = CenterUserResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckCenterUserIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
