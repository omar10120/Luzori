<?php

namespace App\Http\Controllers\CenterAPI\Report;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Reports\SalesReport\SalesReportResource;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\UserUsedCard;
use App\Models\UserUsedDiscount;
use App\Models\BuyProduct;
use App\Models\UserWallet;
use Illuminate\Http\Response;

class SalesReportController extends Controller
{
    public function sales(Request $request)
    {
        $temp_branches = Branch::query();
        if (get_user_role() != 1) {
            $temp_branches->where('id', auth('center_api')->user()->branch_id);
        }

        $selected_branch = $request->branch_id;
        if (!empty($request->year)) {
            $temp_report = Booking::whereRaw('YEAR(booking_date)="' . $request->year . '"')
                ->whereRaw('MONTH(booking_date)="' . $request->month . '"')
                ->with('details');
            if (!empty($request->branch_id)) {
                $temp_report->where('branch_id', $request->branch_id);
            }
            $report = $temp_report->get();
            $payments_type = get_payment_types();

            $result = [];
            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            for ($i = 1; $i <= $days; $i++) {
                $month = ($request->month <= 9) ? "0" . $request->month : $request->month;
                $day = ($i <= 9) ? "0" . $i : $i;
                $date = $request->year . "-" . $month . "-" . $day;
                $temp = [];
                foreach ($payments_type as $index => $value) {
                    $temp[$index] = 0;
                }
                $temp['commission'] = 0;
                $result[$date] = $temp;
            }

            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    if (isset($result[$value->booking_date->format('Y-m-d')])) {
                        $booking_date_str = $value->booking_date->format('Y-m-d');
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

            $temp_memberShipCards = UserUsedCard::with('booking', 'booking.details')->whereRaw('YEAR(created_at)="' . $request->year . '" and MONTH(created_at)="' . $request->month . '"');
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_api')->user()->branch_id;
                $temp_memberShipCards->whereHas('booking', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
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

            $temp_discount = UserUsedDiscount::with('booking', 'booking.details')->whereRaw('YEAR(created_at)="' . $request->year . '" and MONTH(created_at)="' . $request->month . '"');
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_api')->user()->branch_id;
                $temp_discount->whereHas('booking', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }

            $discount = $temp_discount->get();
            if (!$discount->isEmpty()) {
                foreach ($discount as $static) {
                    $amount = $static->amount;
                    if (!empty($static->booking) && !empty($static->booking->details)) {
                        $selected_payment_type = $static->booking->payment_type;
                        if (empty($static->booking->payment_type)) {
                            $selected_payment_type = "wallet";
                        }
                        $amount = $static->amount;
                        $bookingTotal = collect($static->booking->details)->sum('price');
                        $booking_date_str = $static->booking->booking_date->format('Y-m-d');

                        foreach ($static->booking->details as $detail) {
                            if (isset($result[$booking_date_str])) {
                                if ($static->type == "fixed") {
                                    $user_amount = $amount;
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

            $temp_users_wallets = UserWallet::whereRaw('YEAR(created_at)="' . $request->year . '" and MONTH(created_at)="' . $request->month . '"');
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_api')->user()->branch_id;
                $temp_users_wallets->whereHas('created_by_user', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }

            $users_wallets = $temp_users_wallets->get();
            if (!empty($users_wallets)) {
                foreach ($users_wallets as $users_wallet) {
                    $date = date('Y-m-d', strtotime($users_wallet->created_at));
                    $walletKey = ($users_wallet->wallet_type ?? 'wallet') . '_cp';
                    if (!isset($result[$date][$walletKey])) {
                        $result[$date][$walletKey] = 0;
                    }
                    $result[$date][$walletKey] += $users_wallet->invoiced_amount;

                    if (!empty($users_wallet->commission)) {
                        $result[$date]['commission'] += $users_wallet->commission;
                    }
                }
            }

            $temp_BuyProduct = BuyProduct::select('buy_products.*')
                ->whereRaw('YEAR(buy_products.created_at)="' . $request->year . '" and MONTH(buy_products.created_at)="' . $request->month . '"')
                ->with('details')
                ->join('workers', 'workers.id', '=', 'buy_products.worker_id');
                
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_api')->user()->branch_id;
                if ($branch_id) {
                    $temp_BuyProduct->where('workers.branch_id', $branch_id);
                }
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
                            
                            $product_price = $detail->price;
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
        }

        $data = [
            'result' => $result,
            'payments_type' => $payments_type,
        ];
        $data = SalesReportResource::make($data);

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $data);
    }
}
