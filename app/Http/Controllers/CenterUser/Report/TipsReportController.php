<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Enums\SettingEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Worker;
use App\Models\Vacation;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Carbon\Carbon;

class TipsReportController extends Controller
{
    public function tips(Request $request)
    {
        $can = 'VIEW_TIPS_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $all_users = [];

        // ── Distinct years, latest first ──
        $years = Booking::select(DB::raw('DISTINCT YEAR(booking_date) as year'))
            ->orderByDesc('year')
            ->get();

        // ── Default selected year = latest year in the list ──
        $selected_year = $request->get('year') ?? optional($years->first())->year;

        // ── Default selected month = current month ──
        $selected_month = $request->get('month') ?? now()->month;

        // ── Role-aware branch filter ──
        $user     = auth('center_user')->user();
        $userRole = get_user_role();

        if ($userRole == 1) {
            $branch_id_filter = !empty($request->get('branch_id')) ? (int) $request->get('branch_id') : null;
        } else {
            $branch_id_filter = $user->branch_id ? (int) $user->branch_id : null;
        }

        $temp_branches = Branch::query();
        if ($userRole != 1) {
            $temp_branches->where('id', $user->branch_id);
        }
        $branches = $temp_branches->get();

        $selected_branch = $branch_id_filter;
        $selected_worker = $request->get('worker_id');
        $template = "";

        $vacationsWorkerDays = Vacation::get()->toArray();
        $vacationsWorkerIds = [];

        if (!empty($selected_year)) {
            $temp_report = Booking::whereYear('booking_date', $selected_year)
                ->whereMonth('booking_date', $selected_month)
                ->whereHas('details', function ($query) {
                    $query->where('status', 'confirmed');
                })
                ->with(['details' => function ($query) {
                    $query->where('status', 'confirmed');
                }]);

            if ($branch_id_filter) {
                $temp_report->where('branch_id', $branch_id_filter);
            }
            $report = $temp_report->get();

            if (empty($selected_worker)) {
                $temp_users = Worker::query();
                if ($branch_id_filter) {
                    $temp_users->where('branch_id', $branch_id_filter);
                }
            } else {
                $temp_users = Worker::where('id', '=', $selected_worker);
                if ($branch_id_filter) {
                    $temp_users->where('branch_id', $branch_id_filter);
                }
            }

            // Clone the builder for each group so skip/take don't leak between them.
            $firstusers = (clone $temp_users)->skip(0)->take(16)->get();
            $firstusers_last_total = [];
            $firstusers_last_total_price = [];
            $firstusers_last_total_tips = [];
            
            $secondusers = (clone $temp_users)->skip(16)->take(33)->get();
            $secondusers_last_total = [];
            $secondusers_last_total_price = [];
            $secondusers_last_total_tips = [];
            
            // "Rest" starts at 49 (16 + 33).
            $restusers = (clone $temp_users)->skip(49)->take(PHP_INT_MAX)->get();
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
            
            $days = cal_days_in_month(CAL_GREGORIAN, (int) $selected_month, (int) $selected_year);
            for ($i = 1; $i <= $days; $i++) {
                $month = ($selected_month <= 9) ? "0" . $selected_month : $selected_month;
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $selected_year . "-" . $month . "-" . $day;
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
                        $booking_date_str = $value->booking_date->format('Y-m-d');
                        if (isset($result['price'][$booking_date_str])) {
                            if (!$value->details->isEmpty()) {
                                foreach ($value->details as $detail) {
                                    if (!isset($result['price'][$booking_date_str][$detail->worker_id])) {
                                        continue;
                                    }
                                    $result['price'][$booking_date_str][$detail->worker_id] += $detail->price;
                                }
                            }
                        }
                    }
                }

                // Tips from sales.tip + sales.worker_id (not booking_details)
                $temp_sales_tips = Sale::query()
                    ->whereYear('created_at', $selected_year)
                    ->whereMonth('created_at', $selected_month)
                    ->whereNotNull('worker_id')
                    ->where('tip', '>', 0);

                if ($branch_id_filter) {
                    $temp_sales_tips->where('branch_id', $branch_id_filter);
                }

                if (!empty($selected_worker)) {
                    $temp_sales_tips->where('worker_id', $selected_worker);
                }

                foreach ($temp_sales_tips->get(['worker_id', 'tip', 'created_at']) as $sale) {
                    $sale_date_str = Carbon::parse($sale->created_at)->format('Y-m-d');
                    if (!isset($result['tips'][$sale_date_str][$sale->worker_id])) {
                        continue;
                    }
                    $result['tips'][$sale_date_str][$sale->worker_id] += (float) $sale->tip;
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
                    'format' => 'A4',
                    'orientation' => 'landscape',
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
                    'format' => [80, 160],
                    'orientation' => 'portrait',
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
            if ($branch_id_filter) {
                $temp_users->where('branch_id', $branch_id_filter);
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