<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Branch\BranchRequest;
use App\Http\Requests\CenterAPI\Branch\CheckBranchIdRequest;
use App\Http\Resources\BranchResource;
use App\Http\Resources\BranchDetailsResource;
use App\Http\Resources\PaginateDateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BranchController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Branch';

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
            $items = $this->crudService->all($this->model, ['translation'], 0);
        } else {
            $items = $this->crudService->paginate($this->model, ['translation'], 0);
        }
        if ($items) {
            if ($request->page == 0) {
                $items = BranchResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items);
            } else {
                $paginationData = PaginateDateResource::make($items);
                $items = BranchResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckBranchIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, ['translation'], 0);
        if ($item) {
            $item = BranchDetailsResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(BranchRequest $request)
    {
        $newRequest = $request->only(
            'ar',
            'en',
            'longitude',
            'latitude',
        );
        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = BranchResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(BranchRequest $request)
    {
        $newRequest = $request->only(
            'id',
            'ar',
            'en',
            'longitude',
            'latitude',
        );
        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = BranchResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckBranchIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
