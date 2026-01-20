<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Wallet\CheckWalletIdRequest;
use App\Http\Requests\CenterAPI\Wallet\PrintInvoiceRequest;
use App\Http\Requests\CenterAPI\Wallet\UserWalletRequest;
use App\Http\Resources\PrintUserWalletResource;
use App\Http\Resources\UserWalletResource;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use Illuminate\Http\Response;

class UserWalletController extends Controller
{
    private CRUDService $crudService;
    private $model = 'UserWallet';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function showUsers(CheckWalletIdRequest $request)
    {
        $item = $this->crudService->find('Wallet', $request->id, ['users'], 0);
        if ($item) {
            $item = UserWalletResource::collection($item->users);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addUser(UserWalletRequest $request)
    {
        if (!UserWallet::where('wallet_id', $request->wallet_id)->where('user_id', $request->user_id)->exists()) {
            $newRequest = $request->validated();
            $wallet = Wallet::find($request->wallet_id);
            $newRequest['amount'] = $wallet->amount;
            $newRequest['invoiced_amount'] = $wallet->invoiced_amount;
            $item = $this->crudService->updateOrCreate($this->model, $newRequest);
            if ($item) {
                $wallet = Wallet::find($request->wallet_id);
                $wallet->update([
                    'used' => 1
                ]);

                $user = User::find($request->user_id);
                $user->update([
                    'wallet' => $user->wallet + $wallet->amount
                ]);
                $item = UserWalletResource::make($item);
                return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, $item);
            } else {
                return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return MyHelper::responseJSON(__('admin.user_already_has_wallet'), Response::HTTP_BAD_REQUEST);
        }
    }

    public function printInvoice(PrintInvoiceRequest $request)
    {
        $userWallet = UserWallet::with(['wallet', 'user'])->where('user_id', $request->user_id)->where('wallet_id', $request->wallet_id)->first();
        if ($userWallet) {
            $userWallet = PrintUserWalletResource::make($userWallet);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $userWallet);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
