<?php

namespace App\Services;

use App\Models\Worker;

class ReportService
{
    public function getWorkersInDailyReport($bookings)
    {
        $workers = Worker::select('id', 'name')->get();
        foreach ($workers as $worker) {
            $prices = [];
            foreach ($bookings as $booking) {

                // $type = ucwords(str_replace('_', ' ', strtolower($booking->payment_type->name)));
                $type = strtolower($booking->payment_type->name);
                $prices['prices'] = [];
                $prices['total'] = 0;

                if (in_array($worker->id, $booking->details->pluck('worker_id')->toArray())) {
                    $details = $booking->details->where('worker_id', $worker->id);
                    foreach ($details as $detail) {
                        $prices['prices'][] = $detail->service->price;
                        $prices['total'] += $detail->service->price;
                    }
                    $worker[$type] = $prices;
                }
            }
        }
        return $workers;
    }
}
