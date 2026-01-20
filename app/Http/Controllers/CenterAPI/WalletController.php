<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Wallet\WalletRequest;
use App\Http\Requests\CenterAPI\Wallet\CheckWalletIdRequest;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\WalletResource;
use Illuminate\Http\Response;

class WalletController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Wallet';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all()
    {
        $items = $this->crudService->paginate($this->model, ['users'], 0);
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = WalletResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckWalletIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, ['users'], 0);
        if ($item) {
            $item = WalletResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(WalletRequest $request)
    {
        $newRequest = $request->validated();
        $newRequest['code'] = MyHelper::generateCode(10);
        $newRequest['used'] = 0;

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = WalletResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(WalletRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = WalletResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckWalletIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
