<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\Booking;
use App\Models\BuyProduct;
use App\Models\UserWallet;
use App\Models\UserPackage;
use App\Models\UserUsedCard;
use App\Models\UserUsedDiscount;
use Carbon\Carbon;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class ExpenseReportController extends Controller
{
    public function expense_report(Request $request)
    {
        $can = 'VIEW_EXPENSE_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $start_date = $request->start_date ?? now()->format('Y-m-d');
        $end_date = $request->end_date ?? now()->format('Y-m-d');
        $selected_branch = $request->branch_id;

        // Calculate total days between start and end date
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

        $expenses = [];
        $totalExpenses = 0;
        $dailyExpenseRate = 0;
        $incomeData = [];
        $totalIncome = 0;
        $netProfit = 0;

        if (!empty($start_date) && !empty($end_date)) {
            // Get expenses within date range
            $temp_expenses = Expense::where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date, $end_date])
                      ->orWhereBetween('end_date', [$start_date, $end_date])
                      ->orWhere(function ($q) use ($start_date, $end_date) {
                          $q->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                      });
            });

            if ($selected_branch) {
                $temp_expenses->where('branch_id', $selected_branch);
            } elseif (get_user_role() != 1) {
                $temp_expenses->where('branch_id', auth('center_user')->user()->branch_id);
            }

            $expenses = $temp_expenses->with(['branch', 'supplier'])->get();

            // Calculate total expenses and daily rates
            foreach ($expenses as $expense) {
                $expenseStartDate = Carbon::parse($expense->start_date);
                $expenseEndDate = Carbon::parse($expense->end_date);
                
                // Calculate how many days this expense covers within our date range
                $overlapStart = $expenseStartDate->max($startDate);
                $overlapEnd = $expenseEndDate->min($endDate);
                $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                
                // Calculate daily rate for this expense
                $expenseTotalDays = $expenseStartDate->diffInDays($expenseEndDate) + 1;
                $dailyRate = $expense->amount / $expenseTotalDays;
                
                // Add proportional amount for the overlap period
                $totalExpenses += $dailyRate * $overlapDays;
            }

            // Calculate daily expense rate
            $dailyExpenseRate = $totalDays > 0 ? $totalExpenses / $totalDays : 0;

            // Get income data from daily reports logic
            $incomeData = $this->getIncomeData($start_date, $end_date, $selected_branch);
            $totalIncome = $incomeData['total_income'];
            $netProfit = $totalIncome - $totalExpenses;
        }

        $template = (string)view('CenterUser.SubViews.Report.template.expense_report', compact(
            'start_date',
            'end_date',
            'totalDays',
            'expenses',
            'totalExpenses',
            'dailyExpenseRate',
            'incomeData',
            'totalIncome',
            'netProfit',
            'selected_branch'
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

            $pdf = Pdf::loadview('CenterUser.SubViews.Report.pdf.expense_report', compact(
                'template',
            ), [], $options);
            return $pdf->stream('expense_report.pdf');
        }

        $temp_branches = Branch::query();
        if (get_user_role() != 1) {
            $temp_branches->where('id', auth('center_user')->user()->branch_id);
        }
        $branches = $temp_branches->get();

        return view('CenterUser.SubViews.Report.expense_report', compact(
            'start_date',
            'end_date',
            'template',
            'branches',
            'selected_branch',
            'request',
        ));
    }

    private function getIncomeData($start_date, $end_date, $selected_branch)
    {
        $totalIncome = 0;
        $incomeByDate = [];
        $incomeByType = [];
        $branch_id_filter = $selected_branch ?: (get_user_role() != 1 ? auth('center_user')->user()->branch_id : null);
        $bookingWithDiscount = [];

        $membershipCardsQuery = UserUsedCard::with(['booking', 'booking.details' => function ($query) {
            $query->where('status', 'confirmed');
        }])
            ->whereHas('booking.details', function ($query) {
                $query->where('status', 'confirmed');
            })
            ->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);

        if ($branch_id_filter) {
            $membershipCardsQuery->whereHas('booking', function ($query) use ($branch_id_filter) {
                $query->where('branch_id', $branch_id_filter);
            });
        }

        foreach ($membershipCardsQuery->get() as $memberShipCard) {
            if (empty($memberShipCard->booking) || empty($memberShipCard->booking->details)) {
                continue;
            }

            foreach ($memberShipCard->booking->details as $detail) {
                $userAmount = ($detail->price * $memberShipCard->amount) / 100;
                $bookingId = $memberShipCard->booking->id;
                $bookingWithDiscount[$bookingId][$detail->worker_id][$detail->service_id] =
                    ($bookingWithDiscount[$bookingId][$detail->worker_id][$detail->service_id] ?? 0) + $userAmount;
            }
        }

        $discountQuery = UserUsedDiscount::with(['booking', 'booking.details' => function ($query) {
            $query->where('status', 'confirmed');
        }])
            ->whereHas('booking.details', function ($query) {
                $query->where('status', 'confirmed');
            })
            ->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);

        if ($branch_id_filter) {
            $discountQuery->whereHas('booking', function ($query) use ($branch_id_filter) {
                $query->where('branch_id', $branch_id_filter);
            });
        }

        foreach ($discountQuery->get() as $discount) {
            if (empty($discount->booking) || empty($discount->booking->details)) {
                continue;
            }

            foreach ($discount->booking->details as $detail) {
                $userAmount = $discount->type === 'fixed'
                    ? $discount->amount
                    : ($detail->price * $discount->amount) / 100;

                $bookingId = $discount->booking->id;
                $bookingWithDiscount[$bookingId][$detail->worker_id][$detail->service_id] =
                    ($bookingWithDiscount[$bookingId][$detail->worker_id][$detail->service_id] ?? 0) + $userAmount;
            }
        }

        $bookingsQuery = Booking::whereBetween('booking_date', [$start_date, $end_date])
            ->whereHas('details', function ($query) {
                $query->where('status', 'confirmed');
            })
            ->with(['details' => function ($query) {
                $query->where('status', 'confirmed');
            }]);

        if ($branch_id_filter) {
            $bookingsQuery->where('branch_id', $branch_id_filter);
        }

        foreach ($bookingsQuery->get() as $booking) {
            $date = $booking->booking_date->format('Y-m-d');
            $incomeByDate[$date] = $incomeByDate[$date] ?? 0;
            $paymentType = empty($booking->payment_type) ? 'wallet' : $booking->payment_type;

            foreach ($booking->details as $detail) {
                $price = $detail->price;
                $freePrice = $bookingWithDiscount[$booking->id][$detail->worker_id][$detail->service_id] ?? 0;

                if ($detail->is_free == 1) {
                    $freePrice += $detail->price;
                }

                $amount = $price - $freePrice;
                $incomeByDate[$date] += $amount;
                $totalIncome += $amount;

                if ($paymentType === 'multiple' && !empty($booking->payment_methods)) {
                    $bookingTotal = collect($booking->details)->sum('price');
                    if ($bookingTotal > 0) {
                        foreach ($booking->payment_methods as $pm) {
                            $method = $pm['method'] ?? null;
                            $pmAmount = floatval($pm['amount'] ?? 0);
                            if ($method && $pmAmount > 0) {
                                $proportion = $pmAmount / $bookingTotal;
                                $distributedAmount = $amount * $proportion;
                                $incomeByType[$method] = ($incomeByType[$method] ?? 0) + $distributedAmount;
                            }
                        }
                    }
                } else {
                    $incomeByType[$paymentType] = ($incomeByType[$paymentType] ?? 0) + $amount;
                }
            }
        }

        $productsQuery = BuyProduct::select('buy_products.*')
            ->whereRaw('DATE(buy_products.created_at) BETWEEN ? AND ?', [$start_date, $end_date])
            ->with('details')
            ->join('workers', 'workers.id', '=', 'buy_products.worker_id');

        if ($branch_id_filter) {
            $productsQuery->where('workers.branch_id', $branch_id_filter);
        }

        foreach ($productsQuery->get() as $product) {
            $date = date('Y-m-d', strtotime($product->created_at));
            $incomeByDate[$date] = $incomeByDate[$date] ?? 0;

            foreach ($product->details as $detail) {
                $amount = $detail->price ?? ($detail->product?->retail_price && $detail->product?->retail_price > 0
                    ? $detail->product->retail_price
                    : ($detail->product?->supply_price ?? 0));

                if (!empty($product->discount)) {
                    $amount -= ($amount * $product->discount) / 100;
                }

                $incomeByDate[$date] += $amount;
                $totalIncome += $amount;

                $paymentType = $product->payment_type ?? 'cash';
                $incomeByType[$paymentType] = ($incomeByType[$paymentType] ?? 0) + $amount;
            }
        }

        $walletsQuery = UserWallet::select('users_wallets.*')
            ->whereRaw('DATE(users_wallets.created_at) BETWEEN ? AND ?', [$start_date, $end_date])
            ->join('users', 'users.id', '=', 'users_wallets.user_id');

        if ($branch_id_filter) {
            $walletsQuery->where('users.branch_id', $branch_id_filter);
        }

        $wallets = $walletsQuery->get();

        foreach ($wallets as $wallet) {
            $date = date('Y-m-d', strtotime($wallet->created_at));
            if (!isset($incomeByDate[$date])) {
                $incomeByDate[$date] = 0;
            }

            $amount = $wallet->invoiced_amount;
            $incomeByDate[$date] += $amount;
            $totalIncome += $amount;

            // Group by wallet type
            $walletType = $wallet->wallet_type ?? 'wallet';
            if (!isset($incomeByType[$walletType])) {
                $incomeByType[$walletType] = 0;
            }
            $incomeByType[$walletType] += $amount;
        }

        // Get package income
        $temp_packages = UserPackage::whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        if ($selected_branch) {
            $temp_packages->whereHas('user', function ($query) use ($selected_branch) {
                return $query->where('branch_id', $selected_branch);
            });
        } elseif (get_user_role() != 1) {
            $temp_packages->whereHas('user', function ($query) {
                return $query->where('branch_id', auth('center_user')->user()->branch_id);
            });
        }

        $packages = $temp_packages->get();
        foreach ($packages as $package) {
            $date = date('Y-m-d', strtotime($package->created_at));
            if (!isset($incomeByDate[$date])) {
                $incomeByDate[$date] = 0;
            }

            $amount = $package->price;
            $incomeByDate[$date] += $amount;
            $totalIncome += $amount;

            if (!isset($incomeByType['package'])) {
                $incomeByType['package'] = 0;
            }
            $incomeByType['package'] += $amount;
        }

        return [
            'total_income' => $totalIncome,
            'income_by_date' => $incomeByDate,
            'income_by_type' => $incomeByType
        ];
    }
}

