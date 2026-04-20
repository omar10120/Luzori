<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\UserPackage\CheckUserPackageIdRequest;
use App\Http\Requests\CenterAPI\UserPackage\UserPackageRequest;
use App\Http\Resources\UserPackageResource;
use App\Http\Resources\PaginateDateResource;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class UserPackageController extends Controller
{
    private CRUDService $crudService;
    private $model = 'UserPackage';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all()
    {
        $items = $this->crudService->paginate($this->model, ['user', 'package.translation'], 0);
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = UserPackageResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckUserPackageIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, ['user', 'package.translation'], 0);
        if ($item) {
            $item = UserPackageResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(UserPackageRequest $request)
    {
        $package = Package::find($request->package_id);
        $user = User::find($request->user_id);

        if ($request->package_type == 'Wallet') {
            if ($user->wallet < $package->price) {
                return MyHelper::responseJSON(__('admin.insufficient_balance'), Response::HTTP_BAD_REQUEST);
            }
            $user->update([
                'wallet' => $user->wallet - $package->price
            ]);
        }

        $newRequest = $request->validated();
        $newRequest['price'] = $package->price;
        $newRequest['status'] = 'active';
        $newRequest['created_by'] = auth('center_api')->id();

        $item = $this->crudService->updateOrCreate($this->model, $newRequest);
        if ($item) {
            $item = UserPackageResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(UserPackageRequest $request)
    {
        $userPackage = UserPackage::find($request->id);
        if (!$userPackage) {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_NOT_FOUND);
        }

        // Note: Logic for wallet deduction on edit might be complex if payment method or price changes.
        // Usually, in these systems, assignments once made are semi-final or handled via refunds/new sales.
        // For now, we update the record. If the payment method changed to Wallet, we deduct.
        
        if ($request->package_type == 'Wallet' && $userPackage->package_type != 'Wallet') {
            $package = Package::find($request->package_id);
            $user = User::find($request->user_id);
            if ($user->wallet < $package->price) {
                return MyHelper::responseJSON(__('admin.insufficient_balance'), Response::HTTP_BAD_REQUEST);
            }
            $user->update([
                'wallet' => $user->wallet - $package->price
            ]);
        }

        $newRequest = $request->validated();
        $item = $this->crudService->updateOrCreate($this->model, $newRequest);
        
        if ($item) {
            $item = UserPackageResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckUserPackageIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
