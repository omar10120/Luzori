<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Vacation;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class StaffReportController extends Controller
{
    public function staff(Request $request)
    {
        $can = 'VIEW_STAFF_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

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

        $selected_branch = $branch_id_filter;
        $template = "";

        $temp_branches = Branch::query();
        if ($userRole != 1) {
            $temp_branches->where('id', $user->branch_id);
        }
        $branches = $temp_branches->get();

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
            
            $temp_users = Worker::query();
            if ($branch_id_filter) {
                $temp_users->where('branch_id', $branch_id_filter);
            }

            // Clone the builder for each group so skip/take don't leak between them.
            $firstusers  = (clone $temp_users)->skip(0)->take(16)->get();                // workers 0–15
            $firstusers_last_total = [];
            $firstusers_last_total_price = [];
            $firstusers_last_total_tips = [];
            $secondusers = (clone $temp_users)->skip(16)->take(33)->get();               // workers 16–48
            $secondusers_last_total = [];
            $secondusers_last_total_price = [];
            $secondusers_last_total_tips = [];
            // "Rest" starts at 49 (16 + 33). A limit is required so MySQL/MariaDB
            // accepts the OFFSET (bare `OFFSET n` without `LIMIT` is a syntax error).
            $restusers   = (clone $temp_users)->skip(49)->take(PHP_INT_MAX)->get();      // workers 49+
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

            $selected_memberShipCardsUsers = [];
            if (isset($result['price']) && isset($result['tips'])) {
                if (!$report->isEmpty()) {
                    foreach ($report as $value) {
                        $booking_date_str = $value->booking_date->format('Y-m-d');
                        if (isset($result['price'][$booking_date_str]) && isset($result['tips'][$booking_date_str])) {
                            if (!$value->details->isEmpty()) {
                                foreach ($value->details as $detail) {
                                    if (!isset($result['price'][$booking_date_str][$detail->worker_id]) && !isset($result['tips'][$booking_date_str][$detail->worker_id])) {
                                        continue;
                                    }
                                    $price = $detail->price;
                                    $tip = $detail->tip;
                                    $result['price'][$booking_date_str][$detail->worker_id] += $price;
                                    $result['tips'][$booking_date_str][$detail->worker_id] += $tip;
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

            $template = (string)view('CenterUser.SubViews.Report.template.staff_report', compact(
                'result',
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

                $pdf = Pdf::loadview('CenterUser.SubViews.Report.pdf.staff_report', compact(
                    'template'
                ), [], $options);
                return $pdf->stream('staff_report.pdf');
            }
        }

        return view('CenterUser.SubViews.Report.staff_report', compact(
            'years',
            'template',
            'selected_year',
            'selected_month',
            'branches',
            'selected_branch',
            'request',
            'vacationsWorkerIds'
        ));
    }
}