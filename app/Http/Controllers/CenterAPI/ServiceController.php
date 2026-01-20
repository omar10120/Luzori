<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Service\ServiceRequest;
use App\Http\Requests\CenterAPI\Service\CheckServiceIdRequest;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceDetailsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Service';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all(Request $request)
    {
        if ($request->page == 0) {
            $items = $this->crudService->all($this->model, ['translation', 'media'], 0);
        } else {
            $items = $this->crudService->paginate($this->model, ['translation', 'media'], 0);
        }
        if ($items) {
            if ($request->page == 0) {
                $items = ServiceResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items);
            } else {
                $paginationData = PaginateDateResource::make($items);
                $items = ServiceResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckServiceIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, ['translation', 'media'], 0);
        if ($item) {
            $item = ServiceDetailsResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(ServiceRequest $request)
    {
        $newRequest = $request->only(
            'ar',
            'en',
            'rooms_no',
            'free_book',
            'price',
            'is_top',
            'has_commission',
            'image',
        );
        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = ServiceResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(ServiceRequest $request)
    {
        $newRequest = $request->only(
            'id',
            'ar',
            'en',
            'rooms_no',
            'free_book',
            'price',
            'is_top',
            'has_commission',
            'image',
        );
        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = ServiceResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckServiceIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
