<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Discount;
use App\Models\Membership;
use App\Models\Service;
use App\Models\ServiceUser;
use App\Models\User;
use App\Models\UserUsedCard;
use App\Models\UserUsedDiscount;
use App\Models\UserUsedWallet;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function check($mobile, &$reason = NULL)
    {
        if (User::where('phone', $mobile)->exists()) {
            return User::with(['services', 'wallets' => function ($q) {
                $q->with(['wallet'])->join('wallets', 'users_wallets.wallet_id', '=', 'wallets.id')->get();
            }, 'memberships'])->where('phone', $mobile)->first();
        } else {
            $reason = 'USER_NOT_FOUND';
            return NULL;
        }
    }

    public function add($request)
    {
        DB::beginTransaction();
        $request['booking_date'] = date('Y-m-d');
        $request['branch_id'] = Branch::first()->id ?? null;
        $booking = Booking::create($request);
        foreach ($request['service'] as $key => $service) {
            $serviceInfo = Service::find($key);
            $booking->details()->create([
                'service_id' => $serviceInfo->id,
                'price' => $serviceInfo->price,
                '_date' => $service['date'],
                'worker_id' => $service['worker_id'],
                'from_time' => $service['from_time'],
                'to_time' => $service['to_time'],
                'commission' => isset($service['commission']) && $service['commission'] !== '' ? $service['commission'] : null,
                'commission_type' => isset($service['commission_type']) && $service['commission_type'] !== '' ? $service['commission_type'] : null,
            ]);

            if (User::where('phone', $request['mobile'])->exists()) {
                $user = User::where('phone', $request['mobile'])->first();
                ServiceUser::create([
                    'user_id' => $user->id,
                    'service_id' => $key
                ]);
            }
        }

        if (isset($request['discount_id'])) {
            $discount = Discount::find($request['discount_id']);
            if (User::where('phone', $request['mobile'])->exists()) {
                $user = User::where('phone', $request['mobile'])->first();
                UserUsedDiscount::create([
                    'code' => $discount->code,
                    'amount' => $discount->amount,
                    'type' => $discount->type,  
                    'user_id' => $user->id,
                    'discountcode_id' => $discount->id,
                    'booking_id' => $booking->id,
                ]);
            } else {
                UserUsedDiscount::create([
                    'code' => $discount->code,
                    'amount' => $discount->amount,
                    'type' => $discount->type,
                    'discountcode_id' => $discount->id,
                    'booking_id' => $booking->id,
                ]);
            }
        }

        if (isset($request['membership_id'])) {
            $membership = Membership::find($request['membership_id']);
            if (User::where('phone', $request['mobile'])->exists()) {
                $user = User::where('phone', $request['mobile'])->first();
                UserUsedCard::create([
                    'code' => $membership->membership_no,
                    'amount' => $membership->percent,
                    'user_id' => $user->id,
                    'membershipcards_id' => $membership->id,
                    'booking_id' => $booking->id,
                ]);
            }
        }

        if (isset($request['wallet_id'])) {
            $wallet = Wallet::find($request['wallet_id']);
            if (User::where('phone', $request['mobile'])->exists()) {
                $user = User::where('phone', $request['mobile'])->first();
                UserUsedWallet::create([
                    'amount' => $wallet->amount,
                    'user_id' => $user->id,
                    'branch_id' => auth('center_user')->user()->branch_id,
                    'wallet_id' => $wallet->id,
                    'booking_id' => $booking->id,
                ]);
            }
        }
        DB::commit();
        return $booking;
    }
}
