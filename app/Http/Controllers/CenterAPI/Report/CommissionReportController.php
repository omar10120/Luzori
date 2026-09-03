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
            $temp_users = Worker::query()->orderBy('id');
            // Role 1 + empty branch = ALL workers (do not fall back to null auth.branch_id)
            if (!empty($selected_branch)) {
                $temp_users->where('branch_id', $selected_branch);
            } elseif (get_user_role() != 1) {
                $temp_users->where('branch_id', auth('center_api')->user()->branch_id);
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

            $temp_report = Booking::whereYear('booking_date', $request->year)
                ->whereMonth('booking_date', $request->month)
                ->whereHas('details', function ($query) {
                    $query->where('status', 'confirmed');
                })
                ->with(['details' => function ($query) {
                    $query->where('status', 'confirmed');
                }]);
            if (!empty($request->branch_id)) {
                $temp_report->where('branch_id', $request->branch_id);
            } elseif (get_user_role() != 1) {
                $temp_report->where('branch_id', auth('center_api')->user()->branch_id);
            }

            $report = $temp_report->get();
            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    $booking_date_str = $value->booking_date->format('Y-m-d');
                    if (!isset($result[$booking_date_str]) || $value->details->isEmpty()) {
                        continue;
                    }
                    foreach ($value->details as $detail) {
                        if (!isset($result[$booking_date_str][$detail->worker_id])) {
                            continue;
                        }
                        $commissionAmount = $this->resolveCommissionAmount($detail);
                        if ($commissionAmount == 0) {
                            continue;
                        }
                        $result[$booking_date_str][$detail->worker_id] += $commissionAmount;
                        $users_with_totals[$detail->worker_id] += $commissionAmount;
                        $result[$booking_date_str]["total"] += $commissionAmount;
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

            $firstusers = $users->slice(0, 16)->values();
            $secondusers = $users->slice(16, 16)->values();
            $restusers = $users->slice(32)->values();
        }

        $data = [
            'result' => $result,
            'firstusers' => $firstusers ?? collect(),
            'secondusers' => $secondusers ?? collect(),
            'restusers' => $restusers ?? collect(),
            'users_with_totals' => $users_with_totals ?? []
        ];

        $data = CommissionReportResource::make($data);
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $data);
    }

    private function resolveCommissionAmount($detail): float
    {
        if ($detail->commission === null || $detail->commission === '') {
            return 0;
        }

        $commission = floatval($detail->commission);
        if ($commission == 0) {
            return 0;
        }

        if ($detail->commission_type === 'fixed') {
            return $commission;
        }

        return (floatval($detail->price) * $commission) / 100;
    }
}
