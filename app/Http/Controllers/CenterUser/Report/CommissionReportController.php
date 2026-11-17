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
        $users_with_totals = [];

        $temp_branches = Branch::query();
        if ($userRole != 1) {
            $temp_branches->where('id', $user->branch_id);
        }
        $branches = $temp_branches->get();
        
        $template = "";
        if (!empty($selected_year)) {
            $temp_users = Worker::query()->orderBy('id');

            if ($branch_id_filter) {
                $temp_users->where('branch_id', $branch_id_filter);
            }

            $users = $temp_users->get();
            Log::info('Commission report workers', [
                'count' => $users->count(),
                'ids' => $users->pluck('id')->all(),
                'branch_id' => $branch_id_filter ?: 'all',
            ]);

            $result = [];
            $days = cal_days_in_month(CAL_GREGORIAN, (int) $selected_month, (int) $selected_year);
            for ($i = 1; $i <= $days; $i++) {
                $month = ($selected_month <= 9) ? "0" . $selected_month : $selected_month;
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $selected_year . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($users as $value) {
                    $temp[$value->id] = 0;
                    $users_with_totals[$value->id] = 0;
                }
                $result[$date] = $temp;
                $result[$date]["total"] = 0;
            }

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

            $temp_users_wallets = UserWallet::whereRaw('YEAR(created_at)="' . $selected_year . '" and MONTH(created_at)="' . $selected_month . '"');
            if ($branch_id_filter) {
                $temp_users_wallets->whereHas('created_by_user', function ($query) use ($branch_id_filter) {
                    return $query->where('branch_id', $branch_id_filter);
                });
            }
            $users_wallets = $temp_users_wallets->get();
            if (!$users_wallets->isEmpty()) {
                foreach ($users_wallets as $users_wallet) {
                    $date = \Carbon\Carbon::parse($users_wallet->created_at)->format('Y-m-d');
                    if (!empty($users_wallet->commission) && isset($result[$date][$users_wallet->worker_id])) {
                        $walletBase = floatval($users_wallet->invoiced_amount ?? $users_wallet->amount ?? 0);
                        $commissionAmount = resolve_stored_commission($users_wallet->commission, $users_wallet->commission_type, $walletBase, false);
                        $result[$date][$users_wallet->worker_id] += $commissionAmount;
                        $users_with_totals[$users_wallet->worker_id] += $commissionAmount;
                        $result[$date]["total"] += $commissionAmount;
                    }
                }
            }

            $temp_BuyProduct = BuyProduct::whereRaw('YEAR(created_at)="' . $selected_year . '" and MONTH(created_at)="' . $selected_month . '"')->with('details');
            if ($branch_id_filter) {
                $temp_BuyProduct->whereHas('created_by_user', function ($query) use ($branch_id_filter) {
                    return $query->where('branch_id', $branch_id_filter);
                });
            }
            $BuyProduct = $temp_BuyProduct->get();
            if (!$BuyProduct->isEmpty()) {
                foreach ($BuyProduct as $BuyProduct_item) {
                    $date = \Carbon\Carbon::parse($BuyProduct_item->created_at)->format('Y-m-d');
                    $orderPrice = 0;
                    foreach ($BuyProduct_item->details as $detail) {
                        $linePrice = floatval($detail->price);
                        if (!empty($BuyProduct_item->discount)) {
                            $linePrice -= ($linePrice * $BuyProduct_item->discount) / 100;
                        }
                        $orderPrice += $linePrice;
                    }
                    if (!empty($BuyProduct_item->commission) && isset($result[$date][$BuyProduct_item->worker_id])) {
                        $commissionAmount = resolve_stored_commission($BuyProduct_item->commission, $BuyProduct_item->commission_type, $orderPrice, false);
                        $result[$date][$BuyProduct_item->worker_id] += $commissionAmount;
                        $result[$date]["total"] += $commissionAmount;
                        $users_with_totals[$BuyProduct_item->worker_id] += $commissionAmount;
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