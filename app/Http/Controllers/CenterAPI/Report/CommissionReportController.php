<?php

namespace App\Http\Controllers\CenterAPI\Report;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Reports\CommissionReport\CommissionReportResource;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Worker;
use App\Models\BuyProduct;
use App\Models\UserWallet;
use Illuminate\Http\Response;

class CommissionReportController extends Controller
{
    public function commissions(Request $request)
    {
        $selected_branch = $request->branch_id;
        $result = [];

        if (!empty($request->year)) {
            $temp_users = Worker::query();
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_api')->user()->branch_id;
                $temp_users->where('branch_id', $branch_id);
            }
            $users = $temp_users->get();

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            for ($i = 1; $i <= $days; $i++) {
                $month = ($request->month <= 9) ? "0" . $request->month : $request->month;
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $request->year . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($users as $value) {
                    $temp[$value->id] = 0;
                    $users_with_totals[$value->id] = 0;
                }
                $result[$date] = $temp;
                $result[$date]["total"] = 0;
            }

            $temp_report = Booking::whereRaw('YEAR(booking_date)="' . $request->year . '"')
                ->whereRaw('MONTH(booking_date)="' . $request->month . '"')
                ->with('details');
            if (!empty($request->branch_id)) {
                $temp_report->where('branch_id', $request->branch_id);
            }

            $report = $temp_report->get();
            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    if (isset($result[$value->booking_date])) {
                        if (!$value->details->isEmpty()) {
                            foreach ($value->details as $detail) {
                                if (isset($result[$value->booking_date][$detail->worker_id])) {
                                    $result[$value->booking_date][$detail->worker_id] += $detail->commission;
                                    $users_with_totals[$detail->worker_id] += $detail->commission;
                                    $result[$value->booking_date]["total"] += $detail->commission;
                                }
                            }
                        }
                    }
                }
            }

            $temp_users_wallets = UserWallet::whereRaw('YEAR(created_at)="' . $request->year . '" and MONTH(created_at)="' . $request->month . '"');

            if ($selected_branch) {
                $branch_id = $selected_branch;
                $temp_users_wallets->whereHas('created_by_user', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }

            $users_wallets = $temp_users_wallets->get();
            if (!empty($users_wallets)) {
                foreach ($users_wallets as $users_wallet) {
                    $date = date('Y-m-d', strtotime($users_wallet->created_at));
                    if (!empty($users_wallet->commission)) {
                        if (isset($result[$date][$users_wallet->worker_id])) {
                            $result[$date][$users_wallet->worker_id] += $users_wallet->commission;
                            $users_with_totals[$users_wallet->worker_id] += $users_wallet->commission;
                            $result[$date]["total"] += $users_wallet->commission;
                        }
                    }
                }
            }

            $temp_BuyProduct = BuyProduct::whereRaw('YEAR(created_at)="' . $request->year . '" and MONTH(created_at)="' . $request->month . '"')->with('details');
            if ($selected_branch) {
                $branch_id = $selected_branch;
                $temp_BuyProduct->whereHas('created_by_user', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }

            $BuyProduct = $temp_BuyProduct->get();
            if (!empty($BuyProduct)) {
                foreach ($BuyProduct as $BuyProduct_item) {
                    $date = date('Y-m-d', strtotime($BuyProduct_item->created_at));
                    if (!empty($BuyProduct_item->commission)) {
                        if (isset($result[$date][$BuyProduct_item->worker_id])) {
                            $result[$date][$BuyProduct_item->worker_id] += $BuyProduct_item->commission;
                            $result[$date]["total"] += $BuyProduct_item->commission;
                            $users_with_totals[$BuyProduct_item->worker_id] += $BuyProduct_item->commission;
                        }
                    }
                }
            }

            $firstusers = $temp_users->skip(0)->take(16)->get();
            $secondusers = $temp_users->skip(16)->take(33)->get();
            $restusers = $temp_users->skip(33)->get();
        }

        $data = [
            'result' => $result,
            'firstusers' => $firstusers,
            'secondusers' => $secondusers,
            'restusers' => $restusers,
            'users_with_totals' => $users_with_totals
        ];

        $data = CommissionReportResource::make($data);
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $data);
    }
}
