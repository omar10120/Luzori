<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Enums\SettingEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\Worker;
use App\Models\Vacation;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class TipsReportController extends Controller
{
    public function tips(Request $request)
    {
        $can = 'VIEW_TIPS_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $all_users = [];
        $years = Booking::select(DB::raw('DISTINCT YEAR(booking_date) as year'))->get();
        $selected_year = $request->get('year');
        $selected_month = $request->get('month');
        $template = "";
        $temp_branches = Branch::query();
        if (get_user_role() != 1) {
            $temp_branches->where('id', auth('center_user')->user()->branch_id);
        }
        $branches = $temp_branches->get();
        $selected_branch = $request->get('branch_id');
        $selected_worker = $request->get('worker_id');

        $vacationsWorkerDays = Vacation::get()->toArray();
        $vacationsWorkerIds = [];
        if (!empty($request->year)) {
            $temp_report = Booking::whereRaw('YEAR(booking_date)="' . $request->get('year') . '"')
                ->whereRaw('MONTH(booking_date)="' . $request->get('month') . '"')
                ->with('details');
            if (!empty($request->get('branch_id'))) {
                $temp_report->where('branch_id', $request->get('branch_id'));
            }
            $report = $temp_report->get();
            if (empty($selected_worker)) {
                $temp_users = Worker::query();
                if (get_user_role() != 1) {
                    $temp_users->where('branch_id', auth('center_user')->user()->branch_id);
                }
            } else {
                $temp_users = Worker::where('id', '=', $selected_worker);
                if (get_user_role() != 1) {
                    $temp_users->where('branch_id', auth('center_user')->user()->branch_id);
                }
            }
            if (!empty($selected_branch)) {
                $temp_users->where('branch_id', $selected_branch);
            }
            $firstusers = $temp_users->skip(0)->take(16)->get();
            $firstusers_last_total = [];
            $firstusers_last_total_price = [];
            $firstusers_last_total_tips = [];
            $secondusers = $temp_users->skip(16)->take(33)->get();
            $secondusers_last_total = [];
            $secondusers_last_total_price = [];
            $secondusers_last_total_tips = [];
            $restusers = $temp_users->skip(33)->get();
            $restusers_last_total = [];
            $restusers_last_total_price = [];
            $restusers_last_total_tips = [];
            foreach ($firstusers as $index => $value) {
                $firstusers_last_total[$value->id] = 0;
                $firstusers_last_total_price[$value->id] = 0;
                $firstusers_last_total_tips[$value->id] = 0;
            }
            foreach ($secondusers as $index => $value) {
                $secondusers_last_total[$value->id] = 0;
                $secondusers_last_total_price[$value->id] = 0;
                $secondusers_last_total_tips[$value->id] = 0;
            }
            foreach ($restusers as $index => $value) {
                $restusers_last_total[$value->id] = 0;
                $restusers_last_total_price[$value->id] = 0;
                $restusers_last_total_tips[$value->id] = 0;
            }
            $result = [];

            $memberShipCardsUsers = [];
            $discountUsers = [];
            $days = cal_days_in_month(CAL_GREGORIAN, $request->get('month'), $request->get('year'));
            for ($i = 1; $i <= $days; $i++) {
                $month = ($request->get('month') <= 9) ? "0" . $request->get('month') : $request->get('month');
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $request->get('year') . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($firstusers as $index => $value) {
                    $temp[$value->id] = 0;
                }
                foreach ($secondusers as $index => $value) {
                    $temp[$value->id] = 0;
                }
                foreach ($restusers as $index => $value) {
                    $temp[$value->id] = 0;
                }
                $result['price'][$date] = $temp;
                $result['tips'][$date] = $temp;
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

            if (isset($result['price']) && isset($result['tips'])) {
                if (!$report->isEmpty()) {
                    foreach ($report as $value) {
                        $price = 0;
                        $tip = 0;
                        if (isset($result['price'][$value->booking_date]) && isset($result['tips'][$value->booking_date])) {
                            if (!$value->details->isEmpty()) {
                                foreach ($value->details as $detail) {
                                    if (!isset($result['price'][$value->booking_date][$detail->worker_id]) && !isset($result['tips'][$value->booking_date][$detail->worker_id])) {
                                        continue;
                                    }
                                    $price = $detail->price;
                                    $tip = $detail->tip;
                                    $result['price'][$value->booking_date][$detail->worker_id] += $price;
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

            $tips = Setting::where('key', SettingEnum::tips->value)->first()->value;

            $template = (string)view('CenterUser.SubViews.Report.template.tips_report', compact(
                'result',
                'tips',
                'firstusers',
                'secondusers',
                'restusers',
                'firstusers_last_total',
                'firstusers_last_total_price',
                'firstusers_last_total_tips',
                'secondusers_last_total',
                'secondusers_last_total_price',
                'secondusers_last_total_tips',
                'restusers_last_total',
                'restusers_last_total_price',
                'restusers_last_total_tips',
                'vacationsWorkerIds'
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

                $pdf = Pdf::loadView('CenterUser.SubViews.Report.pdf.tips_report', compact(
                    'template'
                ), [], $options);
                return $pdf->stream('tips_report.pdf');
            }
            if (isset($request->is_print)) {

                $options = [
                    'format' => [80, 160], // Custom paper size (width, height) in points
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

                $pdf = Pdf::loadView('CenterUser.SubViews.Report.worker_tips_report', compact(
                    'template',
                    'selected_worker',
                    'result',
                    'firstusers',
                    'firstusers_last_total',
                ), [], $options);

                return $pdf->stream('worker_tips_report.pdf');
            }
        }
        if (empty($all_users)) {
            $temp_users = Worker::query();
            if (get_user_role() != 1) {
                $temp_users->where('branch_id', auth('center_user')->user()->branch_id);
            }
            $all_users = $temp_users->get();
        }
        return view('CenterUser.SubViews.Report.tips_report', compact(
            'years',
            'template',
            'selected_year',
            'selected_month',
            'branches',
            'all_users',
            'selected_branch',
            'selected_worker',
            'request',
            'vacationsWorkerIds'
        ));
    }

    public function getUsersByBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        $users = Worker::where('branch_id', $branchId)->get(['id', 'name']);
        return response()->json(['users' => $users]);
    }
}
