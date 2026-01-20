<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\Booking;
use App\Models\BuyProduct;
use App\Models\UserWallet;
use Carbon\Carbon;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class ExpenseReportController extends Controller
{
    public function expense_report(Request $request)
    {
        $can = 'VIEW_EXPENSE_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
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

        // Get bookings income
        $temp_bookings = Booking::whereBetween('booking_date', [$start_date, $end_date])
            ->with('details');

        if ($selected_branch) {
            $temp_bookings->where('branch_id', $selected_branch);
        } elseif (get_user_role() != 1) {
            $temp_bookings->where('branch_id', auth('center_user')->user()->branch_id);
        }

        $bookings = $temp_bookings->get();

        foreach ($bookings as $booking) {
            $date = $booking->booking_date;
            if (!isset($incomeByDate[$date])) {
                $incomeByDate[$date] = 0;
            }

            foreach ($booking->details as $detail) {
                $amount = $detail->price;
                if (!empty($detail->discount)) {
                    $amount -= ($amount * $detail->discount) / 100;
                }
                
                $incomeByDate[$date] += $amount;
                $totalIncome += $amount;

                // Group by payment type
                $paymentType = $detail->payment_type ?? 'cash';
                if (!isset($incomeByType[$paymentType])) {
                    $incomeByType[$paymentType] = 0;
                }
                $incomeByType[$paymentType] += $amount;
            }
        }

        // Get product sales income
        $temp_products = BuyProduct::select('buy_products.*')
            ->whereRaw('DATE(buy_products.created_at) BETWEEN ? AND ?', [$start_date, $end_date])
            ->with('details');

        // Handle branch filtering - use LEFT JOIN to handle null created_by
        if ($selected_branch) {
            $temp_products->leftJoin('center_users', 'center_users.id', '=', 'buy_products.created_by')
                ->where(function($query) use ($selected_branch) {
                    $query->where('center_users.branch_id', $selected_branch)
                          ->orWhereNull('buy_products.created_by'); // Include records with null created_by
                });
        } elseif (get_user_role() != 1) {
            $temp_products->leftJoin('center_users', 'center_users.id', '=', 'buy_products.created_by')
                ->where(function($query) {
                    $query->where('center_users.branch_id', auth('center_user')->user()->branch_id)
                          ->orWhereNull('buy_products.created_by'); // Include records with null created_by
                });
        }

        $products = $temp_products->get();
        
        // Debug: Check BuyProducts query
        \Log::info('ExpenseReport BuyProducts Debug', [
            'sql' => $temp_products->toSql(),
            'bindings' => $temp_products->getBindings(),
            'count' => $products->count(),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'selected_branch' => $selected_branch
        ]);
        
        // Debug: Check if there are any BuyProducts at all
        $allProducts = BuyProduct::select('id', 'created_at', 'created_by', 'worker_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        \Log::info('All BuyProducts (last 5)', [
            'count' => $allProducts->count(),
            'records' => $allProducts->toArray()
        ]);
        
        // Debug: Test the exact query without branch filter
        $testQuery = BuyProduct::select('buy_products.*')
            ->whereRaw('DATE(buy_products.created_at) BETWEEN ? AND ?', [$start_date, $end_date])
            ->join('center_users', 'center_users.id', '=', 'buy_products.created_by')
            ->get();
        \Log::info('BuyProducts Query WITHOUT branch filter', [
            'count' => $testQuery->count(),
            'records' => $testQuery->toArray()
        ]);

        foreach ($products as $product) {
            $date = date('Y-m-d', strtotime($product->created_at));
            if (!isset($incomeByDate[$date])) {
                $incomeByDate[$date] = 0;
            }

            foreach ($product->details as $detail) {
                $amount = $detail->price;
                if (!empty($product->discount)) {
                    $amount -= ($amount * $product->discount) / 100;
                }
                
                $incomeByDate[$date] += $amount;
                $totalIncome += $amount;

                // Group by payment type
                $paymentType = $product->payment_type ?? 'cash';
                if (!isset($incomeByType[$paymentType])) {
                    $incomeByType[$paymentType] = 0;
                }
                $incomeByType[$paymentType] += $amount;
            }
        }

        // Get wallet income (excluding commission/tips)
        $temp_wallets = UserWallet::select('users_wallets.*')
            ->whereRaw('DATE(users_wallets.created_at) BETWEEN ? AND ?', [$start_date, $end_date])
            ->join('center_users', 'center_users.id', '=', 'users_wallets.user_id');

        if ($selected_branch) {
            $temp_wallets->where('center_users.branch_id', $selected_branch);
        } elseif (get_user_role() != 1) {
            $temp_wallets->where('center_users.branch_id', auth('center_user')->user()->branch_id);
        }

        $wallets = $temp_wallets->get();

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

        return [
            'total_income' => $totalIncome,
            'income_by_date' => $incomeByDate,
            'income_by_type' => $incomeByType
        ];
    }
}

