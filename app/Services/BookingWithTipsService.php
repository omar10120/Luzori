<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingWithTipsService
{
    public function all()
    {
        $relations = ['branch' => function ($q) {
            $q->with(['translation']);
        }, 'details' => function ($q) {
            $q->with(['worker']);
        }];

        return Booking::with($relations)->where('payment_type', 'tips_visa')->get();
    }

    public function paginate()
    {
        $relations = ['branch' => function ($q) {
            $q->with(['translation']);
        }, 'details' => function ($q) {
            $q->with(['worker']);
        }];

        return Booking::with($relations)->where('payment_type', 'tips_visa')->paginate(10);
    }

    public function add($request)
    {
        DB::beginTransaction();
        $request['booking_date'] = date('Y-m-d');
        $booking = Booking::create($request);
        $booking->details()->create([
            '_date' => $request['date_time'],
            'worker_id' => $request['worker_id'],
            'tip' => $request['tip']
        ]);
        DB::commit();
        return $booking;
    }

    public function edit($request)
    {
        DB::beginTransaction();
        $booking = Booking::find($request['id']);
        $booking->details()->first()->update([
            '_date' => $request['date_time']
        ]);
        DB::commit();
        return $booking;
    }
}
