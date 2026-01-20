<?php

namespace App\Http\Controllers\CenterAPI\Report;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PrintTipsReportResource;
use App\Http\Resources\Reports\TipsReport\TipsReportResource;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\Worker;
use App\Models\Vacation;
use Illuminate\Http\Response;

class TipsReportController extends Controller
{
    public function getWorkersByBranch(Request $request)
    {
        $branch_id = $request->branch_id;
        $workers = Worker::where('branch_id', $branch_id)->get(['id', 'name']);
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $workers);
    }

    public function tips(Request $request)
    {
        $result = [];
        $memberShipCardsUsers = [];
        $discountUsers = [];

        $selected_branch = $request->branch_id;
        $selected_worker = $request->worker_id;

        $vacationsWorkerDays = Vacation::get()->toArray();
        $vacationsWorkerIds = [];

        if (!empty($request->year)) {
            $temp_report = Booking::whereRaw('YEAR(booking_date)="' . $request->year . '"')
                ->whereRaw('MONTH(booking_date)="' . $request->month . '"')
                ->with('details');
            if (!empty($request->branch_id)) {
                $temp_report->where('branch_id', $request->branch_id);
            }
            $report = $temp_report->get();
            if (empty($selected_worker)) {
                $temp_users = Worker::query();
                if (get_user_role() != 1) {
                    $temp_users->where('branch_id', auth('center_api')->user()->branch_id);
                }
            } else {
                $temp_users = Worker::where('id', '=', $selected_worker);
                if (get_user_role() != 1) {
                    $temp_users->where('branch_id', auth('center_api')->user()->branch_id);
                }
            }
            if (!empty($selected_branch)) {
                $temp_users->where('branch_id', $selected_branch);
            }

            $firstusers = $temp_users->skip(0)->take(16)->get();
            $secondusers = $temp_users->skip(16)->take(33)->get();
            $restusers = $temp_users->skip(33)->get();

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            for ($i = 1; $i <= $days; $i++) {
                $month = ($request->month <= 9) ? "0" . $request->month : $request->month;
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $request->year . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($firstusers as $value) {
                    $temp[$value->id] = 0;
                }
                foreach ($secondusers as $value) {
                    $temp[$value->id] = 0;
                }
                foreach ($restusers as $value) {
                    $temp[$value->id] = 0;
                }
                $result[$date] = $temp;
                $memberShipCardsUsers[$date] = $temp;
                $discountUsers[$date] = $temp;
                $vacationsWorkerIds[$date] = array_values(array_filter($vacationsWorkerDays, function ($e) use ($date) {
                    return $e["day"] == $date;
                }));
                $temp = [];
                foreach ($vacationsWorkerIds[$date] as $item) {
                    $temp[] = $item["worker_id"];
                }
                $vacationsWorkerIds[$date] = $temp;
            }

            if (isset($result)) {
                if (!$report->isEmpty()) {
                    foreach ($report as $value) {
                        $tip = 0;
                        if (isset($result[$value->booking_date])) {
                            if (!$value->details->isEmpty()) {
                                foreach ($value->details as $detail) {
                                    if (!isset($result[$value->booking_date][$detail->worker_id])) {
                                        continue;
                                    }
                                    $tip = $detail->tip;
                                    $result[$value->booking_date][$detail->worker_id] += $tip;
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($result)) {
                foreach ($result as $date => $workers_list) {
                    foreach ($workers_list as $worker_id => $price) {
                        if (isset($memberShipCardsUsers[$date][$worker_id])) {
                            $result[$date][$worker_id] -= $memberShipCardsUsers[$date][$worker_id];
                        }
                        if (isset($discountUsers[$date][$worker_id])) {
                            $result[$date][$worker_id] -= $discountUsers[$date][$worker_id];
                        }
                    }
                }
            }

            $tips = Setting::where('key', 'tips')->first()->value;
        }

        $data = [
            'result' => $result,
            'tips' => $tips,
            'firstusers' => $firstusers,
            'secondusers' => $secondusers,
            'restusers' => $restusers,
            'vacationsWorkerIds' => $vacationsWorkerIds
        ];

        $data = TipsReportResource::make($data);
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $data);
    }

    public function printInvoice(Request $request)
    {
        $report = Booking::whereRaw('YEAR(booking_date)="' . $request->year . '"')
            ->whereRaw('MONTH(booking_date)="' . $request->month . '"')
            ->where('branch_id', $request->branch_id)
            ->with('details')->get();

        $firstusers = Worker::where('id', $request->worker_id)->get();

        $result = [];
        $memberShipCardsUsers = [];
        $discountUsers = [];

        $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
        for ($i = 1; $i <= $days; $i++) {
            $month = ($request->month <= 9) ? "0" . $request->month : $request->month;
            $day = ($i <= 9) ? "0" . $i : $i;
            $date = $request->year . "-" . $month . "-" . $day;
            $temp = [];
            foreach ($firstusers as $value) {
                $temp[$value->id] = 0;
            }
            $result['tips'][$date] = $temp;
            $memberShipCardsUsers[$date] = $temp;
            $discountUsers[$date] = $temp;
        }

        if (isset($result['tips'])) {
            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    $tip = 0;
                    if (isset($result['tips'][$value->booking_date])) {
                        if (!$value->details->isEmpty()) {
                            foreach ($value->details as $detail) {
                                if (!isset($result['tips'][$value->booking_date][$detail->worker_id])) {
                                    continue;
                                }
                                $tip = $detail->tip;
                                $result['tips'][$value->booking_date][$detail->worker_id] += $tip;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($result)) {
            foreach ($result as $date => $workers_list) {
                foreach ($workers_list as $worker_id => $price) {
                    if (isset($memberShipCardsUsers[$date][$worker_id])) {
                        $result[$date][$worker_id] -= $memberShipCardsUsers[$date][$worker_id];
                    }
                    if (isset($discountUsers[$date][$worker_id])) {
                        $result[$date][$worker_id] -= $discountUsers[$date][$worker_id];
                    }
                }
            }
        }

        if (!empty($result)) {
            foreach ($result as $date => $workers_list) {
                foreach ($workers_list as $worker_id => $price) {
                    if (isset($memberShipCardsUsers[$date][$worker_id])) {
                        $result[$date][$worker_id] -= $memberShipCardsUsers[$date][$worker_id];
                    }
                    if (isset($discountUsers[$date][$worker_id])) {
                        $result[$date][$worker_id] -= $discountUsers[$date][$worker_id];
                    }
                }
            }
        }

        if ($result) {
            $result = PrintTipsReportResource::make($result);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $result);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
