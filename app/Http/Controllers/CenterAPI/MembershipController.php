<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Membership\MembershipRequest;
use App\Http\Requests\CenterAPI\Membership\CheckMembershipIdRequest;
use App\Http\Resources\MembershipResource;
use App\Http\Resources\PaginateDateResource;
use Illuminate\Http\Response;

class MembershipController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Membership';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all()
    {
        $items = $this->crudService->paginate($this->model, ['user', 'created_user'], 0);
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = MembershipResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckMembershipIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, ['user', 'created_user'], 0);
        if ($item) {
            $item = MembershipResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(MembershipRequest $request)
    {
        $newRequest = $request->validated();
        $newRequest['created_by'] = auth('center_api')->user()->id;

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            $item = MembershipResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(MembershipRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = MembershipResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckMembershipIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
