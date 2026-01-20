<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Package\PackageRequest;
use App\Http\Requests\CenterAPI\Package\CheckPackageIdRequest;
use App\Http\Resources\PackageDetailsResource;
use App\Http\Resources\PackageResource;
use App\Http\Resources\PaginateDateResource;
use Illuminate\Http\Response;

class PackageController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Package';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all()
    {
        $items = $this->crudService->paginate($this->model, ['translation', 'packageServicePaid', 'packageServiceFree'], 0);
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = PackageResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckPackageIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, ['translation', 'packageServicePaid', 'packageServiceFree'], 0);
        if ($item) {
            $item = PackageDetailsResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(PackageRequest $request)
    {
        $newRequest = $request->only(
            'ar',
            'en',
            'paid_services',
            'free_services'
        );

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = PackageResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(PackageRequest $request)
    {
        $newRequest = $request->only(
            'id',
            'ar',
            'en',
            'paid_services',
            'free_services'
        );

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = PackageResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckPackageIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
