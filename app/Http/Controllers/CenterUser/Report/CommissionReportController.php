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
use Illuminate\Support\Facades\Log;

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
            $temp_users = Worker::query()->orderBy('id');

            // Role 1 + empty branch = ALL workers.
            // Previous bug: role==1 still forced where(branch_id, auth.branch_id)
            // and when auth.branch_id is null → 0 workers.
            if (!empty($selected_branch)) {
                $temp_users->where('branch_id', $selected_branch);
            } elseif (get_user_role() != 1) {
                $temp_users->where('branch_id', auth('center_user')->user()->branch_id);
            }

            $users = $temp_users->get();
            Log::info('Commission report workers', [
                'count' => $users->count(),
                'ids' => $users->pluck('id')->all(),
                'branch_id' => $selected_branch ?: 'all',
            ]);

            $result = [];
            $days = cal_days_in_month(CAL_GREGORIAN, $request->get('month'), $request->get('year'));
            for ($i = 1; $i <= $days; $i++) {
                $month = ($request->get('month') <= 9) ? "0" . $request->get('month') : $request->get('month');
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $request->get('year') . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($users as $value) {
                    $temp[$value->id] = 0;
                    $users_with_totals[$value->id] = 0;
                }
                $result[$date] = $temp;
                $result[$date]["total"] = 0;
            }

            $temp_report = Booking::whereYear('booking_date', $request->get('year'))
                ->whereMonth('booking_date', $request->get('month'))
                ->whereHas('details', function ($query) {
                    $query->where('status', 'confirmed');
                })
                ->with(['details' => function ($query) {
                    $query->where('status', 'confirmed');
                }]);
            if (!empty($selected_branch)) {
                $temp_report->where('branch_id', $selected_branch);
            } elseif (get_user_role() != 1) {
                $temp_report->where('branch_id', auth('center_user')->user()->branch_id);
            }
            $report = $temp_report->get();
            Log::info('Commission report bookings', [
                'count' => $report->count(),
                'booking_ids' => $report->pluck('id')->all(),
            ]);

            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    $booking_date_str = $value->booking_date->format('Y-m-d');
                    if (!isset($result[$booking_date_str])) {
                        continue;
                    }
                    if ($value->details->isEmpty()) {
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

            $temp_users_wallets = UserWallet::whereRaw('YEAR(created_at)="' . $request->get('year') . '" and MONTH(created_at)="' . $request->get('month') . '"');
            if ($selected_branch) {
                $branch_id = $selected_branch;
                $temp_users_wallets->whereHas('created_by_user', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }
            $users_wallets = $temp_users_wallets->get();
            if (!$users_wallets->isEmpty()) {
                foreach ($users_wallets as $users_wallet) {
                    $date = date('Y-m-d', strtotime($users_wallet->created_at));
                    if (!empty($users_wallet->commission) && isset($result[$date][$users_wallet->worker_id])) {
                        $result[$date][$users_wallet->worker_id] += $users_wallet->commission;
                        $users_with_totals[$users_wallet->worker_id] += $users_wallet->commission;
                        $result[$date]["total"] += $users_wallet->commission;
                    }
                }
            }

            $temp_BuyProduct = BuyProduct::whereRaw('YEAR(created_at)="' . $request->get('year') . '" and MONTH(created_at)="' . $request->get('month') . '"')->with('details');
            if ($selected_branch) {
                $branch_id = $selected_branch;
                $temp_BuyProduct->whereHas('created_by_user', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }
            $BuyProduct = $temp_BuyProduct->get();
            if (!$BuyProduct->isEmpty()) {
                foreach ($BuyProduct as $BuyProduct_item) {
                    $date = date('Y-m-d', strtotime($BuyProduct_item->created_at));
                    if (!empty($BuyProduct_item->commission) && isset($result[$date][$BuyProduct_item->worker_id])) {
                        $result[$date][$BuyProduct_item->worker_id] += $BuyProduct_item->commission;
                        $result[$date]["total"] += $BuyProduct_item->commission;
                        $users_with_totals[$BuyProduct_item->worker_id] += $BuyProduct_item->commission;
                    }
                }
            }

            // Slice from collection — do not reuse skip/take on same builder
            $firstusers = $users->slice(0, 16)->values();
            $secondusers = $users->slice(16, 16)->values();
            $restusers = $users->slice(32)->values();

            $template = (string)view('CenterUser.SubViews.Report.template.commission_report', compact(
                'result',
                'firstusers',
                'secondusers',
                'restusers',
                'users_with_totals'
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

    /**
     * Resolve commission amount from booking detail (percentage or fixed).
     */
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

        // percentage (default / backward compatible)
        return (floatval($detail->price) * $commission) / 100;
    }
}
