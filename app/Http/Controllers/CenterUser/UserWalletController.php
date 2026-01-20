<?php

namespace App\Http\Controllers\CenterUser;

use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\UserWalletRequest;
use App\Models\Setting;
use App\Services\CRUDService;
use App\Models\Wallet;
use App\Models\Worker;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class UserWalletController extends Controller
{
    private CRUDService $crudService;
    private $model = 'UserWallet';
    private $plural = 'users_wallets';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.wallets.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function showUsers(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $title = __('locale.show_users');
        $menu = __('locale.wallets');
        $menu_link = route($this->indexRoute);
        $wallet = Wallet::with(['users' => function ($item) {
            $item->with('user');
        }])->find($request->id);

        return view("CenterUser.SubViews.Wallet.show_user_wallet", compact('title', 'wallet', 'menu', 'menu_link'));
    }

    public function print(Request $request)
    {
        $wallet = Wallet::find($request->wallet_id);
        $user = User::find($request->user_id);

        // if (auth('center_user')->user()->branch_id) {
        //     $user_wallet = UserWallet::where('user_id', $request->user_id)
        //         ->where('wallet_id', $request->wallet_id)
        //         ->where('branch_id', auth('center_user')->user()->branch_id)
        //         ->first();
        // } else {
        //     $user_wallet = UserWallet::where('user_id', $request->user_id)
        //         ->where('wallet_id', $request->wallet_id)
        //         ->first();
        // }
        $user_wallet = UserWallet::where('user_id', $request->user_id)
            ->where('wallet_id', $request->wallet_id)
            ->first();

        $options = [
            'format' => [80, 150], // Custom paper size (width, height) in points
            'orientation' => 'portrait', // or 'landscape'
            'margin-top' => 10,
            'margin-bottom' => 10,
            'margin-left' => 10,
            'margin-right' => 10,
        ];

        $invoice_info = Setting::where('key', SettingEnum::invoice_info->value)->first()->value;
        $template = (string)view('CenterUser.SubViews.Report.template.invoice_info', compact(
            'invoice_info',
        ));

        $view = 'CenterUser.SubViews.Wallet.print';
        $pdf = PDF::loadView($view, compact('user', 'wallet', 'user_wallet', 'template'), [], $options);
        return $pdf->stream('user_wallet.pdf');
    }

    public function create(Request $request)
    {
        $title = __('locale.add_users_to');
        $menu = __('locale.wallets');

        $menu_link = route($this->indexRoute);
        $requestUrl = route($this->updateOrCreateRoute);

        $wallet = Wallet::select('id', 'code')->find($request->id);
        $workers = Worker::all();
        $users = User::all();
        
        $paymentMethods = \App\Models\PaymentMethod::forWallet()->get();
        // $paymentMethods = $this->crudService->all('PaymentMethod')->where('type', 'wallet');


        $view = 'CenterUser.SubViews.Wallet.add_user_wallet';
        return view($view, compact('wallet', 'requestUrl', 'title', 'menu', 'menu_link', 'workers', 'users', 'paymentMethods'));
    }

    public function updateOrCreate(UserWalletRequest $request)
    {
        $responseCode = Response::HTTP_CREATED;
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

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
                return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.wallets.index'));
            } else {
                return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return MyHelper::responseJSON(__('admin.user_already_has_wallet'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
