<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Shift\ShiftRequest;
use App\Http\Requests\CenterAPI\Shift\CheckShiftIdRequest;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\ShiftResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShiftController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Shift';

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
            $items = $this->crudService->all($this->model, [], 0);
        } else {
            $items = $this->crudService->paginate($this->model, [], 0);
        }
        if ($items) {
            if ($request->page == 0) {
                $items = ShiftResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items);
            } else {
                $paginationData = PaginateDateResource::make($items);
                $items = ShiftResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckShiftIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, [], 0);
        if ($item) {
            $item = ShiftResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(ShiftRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = ShiftResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(ShiftRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = ShiftResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckShiftIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
