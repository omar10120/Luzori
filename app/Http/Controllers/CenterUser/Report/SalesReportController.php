<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\UserUsedCard;
use App\Models\UserUsedDiscount;
use App\Models\BuyProduct;
use App\Models\UserWallet;
use App\Models\UserPackage;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class SalesReportController extends Controller
{
    public function sales(Request $request)
    {
        $can = 'VIEW_SALES_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $branch_id = $request->get('branch_id');
        $years = Booking::select(DB::raw('DISTINCT YEAR(booking_date) as year'))->get();
        $selected_year = $request->get('year');
        $selected_month = $request->get('month');
        $temp_branches = Branch::query();
        if (get_user_role() != 1) {
            $temp_branches->where('id', auth('center_user')->user()->branch_id);
        }

        $branches = $temp_branches->get();
        $selected_branch = $request->get('branch_id');
        $branch_id_filter = !empty($selected_branch) ? $selected_branch : null;
        $template = "";
        if (!empty($request->year)) {
            $temp_report = Booking::whereYear('booking_date', $request->get('year'))
                ->whereMonth('booking_date', $request->get('month'))
                ->whereHas('details', function ($query) {
                    $query->where('status', 'confirmed');
                })
                ->with(['details' => function ($query) {
                    $query->where('status', 'confirmed');
                }]);
            // if (!empty($request->get('branch_id'))) {
            //     $temp_report->where('branch_id', $request->get('branch_id'));
            // }
            if ($branch_id_filter) {
                $temp_report->where('branch_id', $branch_id_filter);
            }
            $report = $temp_report->get();
            $payments_type = get_payment_types();
            // Add 'wallet' to payment types list if not already present (for bookings with empty payment_type)
            if (!isset($payments_type['wallet'])) {
                $payments_type['wallet'] = __('locale.wallets');
            }
            if (!isset($payments_type['package'])) {
                $payments_type['package'] = __('locale.packages');
            }
            // Merge dynamic wallet methods used in the selected month so columns exist
            $dynamicWalletTypesQuery = UserWallet::select('users_wallets.wallet_type')
                ->whereRaw('YEAR(users_wallets.created_at)="' . $request->get('year') . '" and MONTH(users_wallets.created_at)="' . $request->get('month') . '"');
            // if (get_user_role() == 1 || $selected_branch) {
            //     $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
            //     $dynamicWalletTypesQuery = $dynamicWalletTypesQuery
            //         ->whereHas('user', function ($query) use ($branch_id) {
            //             return $query->where('branch_id', $branch_id);
            //         });
            // }
            if ($branch_id_filter) {
                $dynamicWalletTypesQuery->whereHas('user', function ($query) use ($branch_id_filter) {
                    $query->where('branch_id', $branch_id_filter);
                });
            }
                $dynamicWalletTypes = $dynamicWalletTypesQuery->pluck('wallet_type')->unique()->toArray();
            foreach ($dynamicWalletTypes as $walletTypeName) {
                if (!array_key_exists($walletTypeName, $payments_type)) {
                    $payments_type[$walletTypeName] = ucfirst(str_replace('_', ' ', $walletTypeName));
                }
            }
            $last_total = [];
            foreach ($payments_type as $index => $value) {
                $last_total[$index] = 0;
            }
            $payments_type['commission'] = __('field.commission');
            $last_total['commission'] = 0;
            $last_total['total_without_free'] = 0;
            $result = [];
            $days = cal_days_in_month(CAL_GREGORIAN, $request->get('month'), $request->get('year'));
            for ($i = 1; $i <= $days; $i++) {
                $month = ($request->get('month') <= 9) ? "0" . $request->get('month') : $request->get('month');
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $request->get('year') . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($payments_type as $index => $value) {
                    $temp[$index] = 0;
                }
                $temp['commission'] = 0;
                $result[$date] = $temp;
            }
            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    $booking_date_str = $value->booking_date->format('Y-m-d');
                    if (isset($result[$booking_date_str])) {
                        $selected_payment_type = $value->payment_type;
                        if (empty($value->payment_type)) {
                            $selected_payment_type = "wallet";
                        }

                        if (!$value->details->isEmpty()) {
                            foreach ($value->details as $detail) {
                                $detail_price = $detail->price;
                                if ($detail->is_free == 1) {
                                    $result[$booking_date_str]['free'] += $detail->price;
                                    $detail_price -= $detail->price;
                                }

                                if ($selected_payment_type === 'multiple' && !empty($value->payment_methods)) {
                                    $bookingTotal = collect($value->details)->sum('price');
                                    if ($bookingTotal > 0) {
                                        foreach ($value->payment_methods as $pm) {
                                            $method = $pm['method'] ?? null;
                                            $pmAmount = floatval($pm['amount'] ?? 0);
                                            if ($method && $pmAmount > 0) {
                                                $proportion = $pmAmount / $bookingTotal;
                                                $distributedPrice = $detail_price * $proportion;

                                                if (!isset($result[$booking_date_str][$method])) {
                                                    $result[$booking_date_str][$method] = 0;
                                                }
                                                $result[$booking_date_str][$method] += $distributedPrice;
                                            }
                                        }
                                    }
                                } else {
                                    if (!isset($result[$booking_date_str][$selected_payment_type])) {
                                        $result[$booking_date_str][$selected_payment_type] = 0;
                                    }
                                    $result[$booking_date_str][$selected_payment_type] += $detail_price;
                                }
                                
                                // Calculate commission based on commission_type
                                if ($detail->commission !== null && $detail->commission !== '') {
                                    $commission_amount = $detail->commission;
                                    if ($detail->commission_type == 'percentage') {
                                        // If percentage, calculate: (price * commission) / 100
                                        $commission_amount = ($detail->price * floatval($detail->commission)) / 100;
                                    } elseif ($detail->commission_type == 'fixed') {
                                        // If fixed, use commission value directly
                                        $commission_amount = floatval($detail->commission);
                                    } else {
                                        // Backward compatibility: if commission_type is null, assume percentage
                                        $commission_amount = ($detail->price * floatval($detail->commission)) / 100;
                                    }
                                    $result[$booking_date_str]['commission'] += $commission_amount;
                                }
                            }
                        }
                    }
                }
            }
            
            $temp_memberShipCards = UserUsedCard::with(['booking', 'booking.details' => function($q) { $q->where('status', 'confirmed'); }])
                ->whereHas('booking.details', function($q) { $q->where('status', 'confirmed'); })
                ->whereYear('created_at', $request->get('year'))
                ->whereMonth('created_at', $request->get('month'));
            // if (get_user_role() == 1 || $selected_branch) {
            //     $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
            //     $temp_memberShipCards->whereHas('booking', function ($query) use ($branch_id) {
            //         return $query->where('branch_id', $branch_id);
            //     });
            // }
            if ($branch_id_filter) {
                $temp_memberShipCards->whereHas('booking', function ($query) use ($branch_id_filter) {
                    $query->where('branch_id', $branch_id_filter);
                });
            }
            $memberShipCards = $temp_memberShipCards->get();
            if (!$memberShipCards->isEmpty()) {
                foreach ($memberShipCards as $memberShipCard) {
                    if (!empty($memberShipCard->booking) && !empty($memberShipCard->booking->details)) {
                        $selected_payment_type = $memberShipCard->booking->payment_type;
                        if (empty($memberShipCard->booking->payment_type)) {
                            $selected_payment_type = "wallet";
                        }
                        $amount = $memberShipCard->amount;
                        $bookingTotal = collect($memberShipCard->booking->details)->sum('price');
                        $booking_date_str = $memberShipCard->booking->booking_date->format('Y-m-d');

                        foreach ($memberShipCard->booking->details as $detail) {
                            if (isset($result[$booking_date_str])) {
                                $user_amount = ($detail->price * $amount) / 100;
                                $result[$booking_date_str]['free'] += $user_amount;

                                if ($selected_payment_type === 'multiple' && !empty($memberShipCard->booking->payment_methods)) {
                                    if ($bookingTotal > 0) {
                                        foreach ($memberShipCard->booking->payment_methods as $pm) {
                                            $method = $pm['method'] ?? null;
                                            $pmAmount = floatval($pm['amount'] ?? 0);
                                            if ($method && $pmAmount > 0) {
                                                $proportion = $pmAmount / $bookingTotal;
                                                $distributedDiscount = $user_amount * $proportion;
                                                if (!isset($result[$booking_date_str][$method])) {
                                                    $result[$booking_date_str][$method] = 0;
                                                }
                                                $result[$booking_date_str][$method] -= $distributedDiscount;
                                            }
                                        }
                                    }
                                } else {
                                    // Ensure the payment type key exists in the result array
                                    if (!isset($result[$booking_date_str][$selected_payment_type])) {
                                        $result[$booking_date_str][$selected_payment_type] = 0;
                                    }
                                    $result[$booking_date_str][$selected_payment_type] -= $user_amount;
                                }
                            }
                        }
                    }
                }
            }
            $temp_discount = UserUsedDiscount::with(['booking', 'booking.details' => function($q) { $q->where('status', 'confirmed'); }])
                ->whereHas('booking.details', function($q) { $q->where('status', 'confirmed'); })
                ->whereYear('created_at', $request->get('year'))
                ->whereMonth('created_at', $request->get('month'));
            // if (get_user_role() == 1 || $selected_branch) {
            //     $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
            //     $temp_discount->whereHas('booking', function ($query) use ($branch_id) {
            //         return $query->where('branch_id', $branch_id);
            //     });
            // }
            if ($branch_id_filter) {
                $temp_discount->whereHas('booking', function ($query) use ($branch_id_filter) {
                    $query->where('branch_id', $branch_id_filter);
                });
            }
            $discount = $temp_discount->get();
            $discountsArr = [];
            if (!$discount->isEmpty()) {
                foreach ($discount as $static) {
            
                    if (!empty($static->booking) && !empty($static->booking->details)) {
            
                        $selected_payment_type = $static->booking->payment_type;
                        if (empty($selected_payment_type)) {
                            $selected_payment_type = 'wallet';
                        }
            
                        $amount = $static->amount;
                        $bookingTotal = collect($static->booking->details)->sum('price');
                        $booking_date_str = $static->booking->booking_date->format('Y-m-d');
            
                        foreach ($static->booking->details as $detail) {
            
                            if (!isset($result[$booking_date_str])) {
                                continue;
                            }
            
                            if ($static->type == 'fixed') {

                                $user_amount = $bookingTotal > 0
                                    ? ($detail->price / $bookingTotal) * $amount
                                    : 0;
                            
                            } else {
                            
                                $user_amount = ($detail->price * $amount) / 100;
                            }
            
                            if ($selected_payment_type === 'multiple' && !empty($static->booking->payment_methods)) {
            
                                if ($bookingTotal > 0) {
                                    foreach ($static->booking->payment_methods as $pm) {
            
                                        $method = $pm['method'] ?? null;
                                        $pmAmount = floatval($pm['amount'] ?? 0);
            
                                        if ($method && $pmAmount > 0) {
            
                                            $proportion = $pmAmount / $bookingTotal;
                                            $distributedDiscount = $user_amount * $proportion;
            
                                            if (!isset($result[$booking_date_str][$method])) {
                                                $result[$booking_date_str][$method] = 0;
                                            }
            
                                            $result[$booking_date_str][$method] -= $distributedDiscount;
                                        }
                                    }
                                }
            
                            } else {
            
                                if (!isset($result[$booking_date_str][$selected_payment_type])) {
                                    $result[$booking_date_str][$selected_payment_type] = 0;
                                }
            
                                $result[$booking_date_str][$selected_payment_type] -= $user_amount;
                            }
                        }
                    }
                }
            }

            $temp_users_wallets = UserWallet::whereRaw('YEAR(users_wallets.created_at)="' . $request->get('year') . '" and MONTH(users_wallets.created_at)="' . $request->get('month') . '"');
            // if (get_user_role() == 1 || $branch_id) {
            //     $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
            //     $temp_users_wallets->whereHas('user', function ($query) use ($branch_id) {
            //         return $query->where('branch_id', $branch_id);
            //     });
            // }
            if ($branch_id_filter) {
                $dynamicWalletTypesQuery->whereHas('user', function ($query) use ($branch_id_filter) {
                    $query->where('branch_id', $branch_id_filter);
                });
            }
            $users_wallets = $temp_users_wallets->get();
            if (!empty($users_wallets)) {
                foreach ($users_wallets as $users_wallet) {
                    $date = date('Y-m-d', strtotime($users_wallet->created_at));
                    $walletKey = ($users_wallet->wallet_type ?? 'wallet');
                    if (!isset($result[$date][$walletKey])) {
                        $result[$date][$walletKey] = 0;
                    }
                    $result[$date][$walletKey] += $users_wallet->invoiced_amount;

                    if (!empty($users_wallet->commission)) {
                        $result[$date]['commission'] += $users_wallet->commission;
                    }
                }
            }

            $temp_users_packages = UserPackage::whereRaw('YEAR(users_packages.created_at)="' . $request->get('year') . '" and MONTH(users_packages.created_at)="' . $request->get('month') . '"');
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
                $temp_users_packages->whereHas('user', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }
            $users_packages = $temp_users_packages->get();
            if (!empty($users_packages)) {
                foreach ($users_packages as $users_package) {
                    $date = date('Y-m-d', strtotime($users_package->created_at));
                    if (!isset($result[$date]['package'])) {
                        $result[$date]['package'] = 0;
                    }
                    $result[$date]['package'] += $users_package->price;
                }
            }

            $temp_BuyProduct = BuyProduct::select('buy_products.*')
                ->whereRaw('YEAR(buy_products.created_at)="' . $request->get('year') . '" and MONTH(buy_products.created_at)="' . $request->get('month') . '"')
                ->with('details')
                ->join('workers', 'workers.id', '=', 'buy_products.worker_id');
                
            // if (get_user_role() == 1 || $selected_branch) {
            //     $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
            //     if ($branch_id) {
            //         $temp_BuyProduct->where('workers.branch_id', $branch_id);
            //     }
            // }
            if ($branch_id_filter) {
                $temp_BuyProduct->where('workers.branch_id', $branch_id_filter);
            }
            $BuyProduct = $temp_BuyProduct->get();
            if (!empty($BuyProduct)) {
                foreach ($BuyProduct as $BuyProduct_item) {
                    $date = date('Y-m-d', strtotime($BuyProduct_item->created_at));
                    if (!$BuyProduct_item->details->isEmpty()) {
                        foreach ($BuyProduct_item->details as $detail) {
                            // Ensure payment type exists in result array
                            if (!isset($result[$date][$BuyProduct_item->payment_type])) {
                                $result[$date][$BuyProduct_item->payment_type] = 0;
                            }
                            
                            // Use stored price from detail, or fallback to product supply_price/retail_price
                            $product_price = $detail->price ?? ($detail->product?->retail_price && $detail->product->retail_price > 0 
                                ? $detail->product->retail_price 
                                : ($detail->product?->supply_price ?? 0));
                            if (!empty($BuyProduct_item->discount)) {
                                $product_price -= ($product_price * $BuyProduct_item->discount) / 100;
                            }
                            $result[$date][$BuyProduct_item->payment_type] += $product_price;
                        }
                    }

                    if (!empty($BuyProduct_item->commission)) {
                        $result[$date]['commission'] += $BuyProduct_item->commission;
                    }
                }
            }
            $template = (string)view('CenterUser.SubViews.Report.template.sales_report', compact(
                'result',
                'payments_type',
                'last_total'
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

                $pdf = Pdf::loadview('CenterUser.SubViews.Report.pdf.sales_report', compact(
                    'template'
                ), [], $options);
                return $pdf->stream('sales_report.pdf');
            }
        }
        return view('CenterUser.SubViews.Report.sales_report', compact(
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
