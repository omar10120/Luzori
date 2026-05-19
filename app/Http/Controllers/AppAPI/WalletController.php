<?php

namespace App\Http\Controllers\AppAPI;

use App\Http\Controllers\Controller;
use App\Models\AppPaymentMethod;
use App\Models\AppUserWallet;
use App\Models\AppUserUsedWallet;
use App\Helpers\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        $user = $request->user();
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'wallet' => $user->wallet
        ]);
    }

    public function paymentMethods(Request $request)
    {
        // Assuming we fetch payment methods that support "wallet" top up
        $methods = AppPaymentMethod::all()->filter(function ($method) {
            return in_array('wallet', $method->types ?? []);
        })->values();

        // If types are not strictly used, we can just return all
        if ($methods->isEmpty()) {
            $methods = AppPaymentMethod::all();
        }

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $methods);
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|integer', // Will validate against payment_methods table if needed
        ]);

        $user = $request->user();

        // Increase user's wallet balance
        $user->wallet += $request->amount;
        $user->save();

        // Record the top-up in users_wallets
        $userWallet = AppUserWallet::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'wallet_type' => 'top_up',
            'invoiced_amount' => $request->amount,
        ]);

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'wallet' => $user->wallet,
            'transaction' => $userWallet
        ]);
    }

    public function buy(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();

        // Increase user's wallet balance
        $user->wallet += $request->amount;
        $user->save();

        // Record the top-up in users_wallets
        $userWallet = AppUserWallet::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'wallet_type' => 'buy_wallet',
            'invoiced_amount' => $request->amount,
        ]);

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'wallet' => $user->wallet,
            'transaction' => $userWallet
        ]);
    }


    public function transactions(Request $request)
    {
        $user = $request->user();
        
        $topUps = AppUserWallet::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        $usages = AppUserUsedWallet::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'top_ups' => $topUps,
            'usages' => $usages
        ]);
    }
}
