<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Worker;
use App\Models\BuyProduct;
use App\Models\UserWallet;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class CommissionReportController extends Controller
{
    public function commissions(Request $request)
    {
        $can = 'VIEW_COMMISSION_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $years = Booking::select(DB::raw('DISTINCT YEAR(booking_date) as year'))->get();
        $selected_year = $request->get('year');
        $selected_month = $request->get('month');
        $temp_branches = Branch::query();
        $users_with_totals = [];
        if (get_user_role() != 1) {
            $temp_branches->where('id', auth('center_user')->user()->branch_id);
        }
        $branches = $temp_branches->get();
        $selected_branch = $request->get('branch_id');
        $template = "";
        if (!empty($request->year)) {
            $temp_users = Worker::query();
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
                $temp_users->where('branch_id', $branch_id);
            }
            $users = $temp_users->get();
            $result = [];
            $days = cal_days_in_month(CAL_GREGORIAN, $request->get('month'), $request->get('year'));
            for ($i = 1; $i <= $days; $i++) {
                $month = ($request->get('month') <= 9) ? "0" . $request->get('month') : $request->get('month');
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $request->get('year') . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($users as $index => $value) {
                    $temp[$value->id] = 0;
                    $users_with_totals[$value->id] = 0;
                }
                $result[$date] = $temp;
                $result[$date]["total"] = 0;
            }
            $temp_report = Booking::whereRaw('YEAR(booking_date)="' . $request->get('year') . '"')
                ->whereRaw('MONTH(booking_date)="' . $request->get('month') . '"')
                ->with('details');
            if (!empty($request->get('branch_id'))) {
                $temp_report->where('branch_id', $request->get('branch_id'));
            }
            $report = $temp_report->get();
            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    $price = 0;
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

            $temp_users_wallets = UserWallet::whereRaw('YEAR(created_at)="' . $request->get('year') . '" and MONTH(created_at)="' . $request->get('month') . '"');
            // if (get_user_role() == 1 || $selected_branch) {
            //     $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
            //     $temp_users_wallets->whereHas('created_by_user', function ($query) use ($branch_id) {
            //         return $query->where('branch_id', $branch_id);
            //     });
            // }
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

            $temp_BuyProduct = BuyProduct::whereRaw('YEAR(created_at)="' . $request->get('year') . '" and MONTH(created_at)="' . $request->get('month') . '"')->with('details');
            // if (get_user_role() == 1 || $selected_branch) {
            //     $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
            //     $temp_BuyProduct->whereHas('created_by_user', function ($query) use ($branch_id) {
            //         return $query->where('branch_id', $branch_id);
            //     });
            // }
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
            $template = (string)view('CenterUser.SubViews.Report.template.commission_report', compact(
                'result',
                'firstusers',
                'secondusers',
                'restusers',
                'users_with_totals'
            ));
            if (isset($request->is_pdf)) {
                $options = [
                    'format' => 'A4', // Custom paper size (width, height) in points
                    'orientation' => 'landscape', // or 'landscape'
                    'margin_top' => 1,
                    'margin_bottom' => 1,
                    'margin_left' => 1,
                    'margin_right' => 1,
                ];

                $pdf = Pdf::loadview('CenterUser.SubViews.Report.pdf.commission_report', compact(
                    'template'
                ), [], $options);
                return $pdf->stream('commission_report.pdf');
            }
        }
        return view('CenterUser.SubViews.Report.commission_report', compact(
            'years',
            'template',
            'selected_year',
            'selected_month',
            'branches',
            'selected_branch',
            'request'
        ));
    }
}
