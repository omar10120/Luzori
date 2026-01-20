<?php

namespace App\Http\Controllers\CenterUser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Worker;
use App\Models\Vacation;
use App\Models\UserUsedCard;
use App\Models\UserUsedDiscount;
use App\Models\BuyProduct;
use App\Models\UserWallet;
use Carbon\Carbon;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class DailyReportController extends Controller
{
    public function daily_report(Request $request)
    {
        $can = 'VIEW_DAILY_REPORTS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $date = $request->date ?? now()->format('Y-m-d');
        $selected_branch = $request->branch_id;

        $result = [];
        $users = [];
        $firstusers = [];
        $secondusers = [];
        $restusers = [];
        $report = "";
        $users_with_prices = [];
        $users_with_commission = [];
        $users_with_tips = [];
        $payments_with_prices = [];
        $payments_type = [];
        $booking_with_discount = [];
        $memberShipCardsUsers = [];
        $selected_memberShipCardsUsers = [];
        $discountUsers = [];
        $selected_discountUsers = [];

        // Initialize product details prices dynamically from payment methods
        $paymentMethods = get_payment_method_names();
        $product_details_prices = [];
        $wallet_details_prices = [];
        
        foreach ($paymentMethods as $method) {
            $product_details_prices[$method] = 0;
            $wallet_details_prices[$method] = 0;
        }

        if (!empty($date)) {
            $date = date('Y-m-d', strtotime($date));
            $temp_report = Booking::whereRaw('booking_date="' . $date . '"')->with('details');
            if ($selected_branch) {
                $temp_report->where('branch_id', $selected_branch);
            }
            $report = $temp_report->get();

            $payments_type = get_payment_types();
            $payments_type_list = [];
            $payments_with_prices = [];
            foreach ($payments_type as $index => $item) {
                $payments_type_list[$index] = 0;
                $payments_with_prices[$index] = [];
            }
            // Add 'wallet' to payment types list if not already present (for bookings with empty payment_type)
            if (!isset($payments_type_list['wallet'])) {
                $payments_type_list['wallet'] = 0;
                $payments_with_prices['wallet'] = [];
            }

            $temp_users = Worker::query();
            if (get_user_role() != 1) {
                $temp_users->where('branch_id', auth('center_user')->user()->branch_id);
            }

            if ($selected_branch) {
                $temp_users->where('branch_id', $selected_branch);
            }

            $users = $temp_users->get();
            $firstusers = $temp_users->skip(0)->take(16)->get();
            $secondusers = $temp_users->skip(16)->take(33)->get();
            $restusers = $temp_users->skip(33)->get();

            foreach ($firstusers as $user) {
                $result[$user->id] = $payments_type_list;
                $users_with_prices[$user->id] = [];
                $users_with_commission[$user->id] = [];
                $users_with_tips[$user->id] = [];
            }

            foreach ($secondusers as $user) {
                $result[$user->id] = $payments_type_list;
                $users_with_prices[$user->id] = [];
                $users_with_commission[$user->id] = [];
                $users_with_tips[$user->id] = [];
            }

            foreach ($restusers as $user) {
                $result[$user->id] = $payments_type_list;
                $users_with_prices[$user->id] = [];
                $users_with_commission[$user->id] = [];
                $users_with_tips[$user->id] = [];
            }


            $temp_memberShipCards = UserUsedCard::with('booking', 'booking.details')->whereRaw('DATE(created_at)="' . $date . '"');
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
                $temp_memberShipCards->whereHas('booking', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }

            $memberShipCards = $temp_memberShipCards->get();
            if (!$memberShipCards->isEmpty()) {
                foreach ($memberShipCards as $memberShipCard) {
                    $amount = $memberShipCard->amount;
                    if (!empty($memberShipCard->booking) && !empty($memberShipCard->booking->details)) {
                        foreach ($memberShipCard->booking->details as $detail) {
                            $user_amount = ($detail->price * $amount) / 100;
                            if (isset($memberShipCardsUsers[$detail->worker_id])) {
                                $memberShipCardsUsers[$detail->worker_id]['amount'] += $user_amount;
                                $memberShipCardsUsers[$detail->worker_id]['amountArr'][] = $user_amount;
                                $memberShipCardsUsers[$detail->worker_id]['detailsArr'][] = $memberShipCard->booking;
                                $memberShipCardsUsers[$detail->worker_id]['codesArr'][] = $memberShipCard->code;
                            } else {
                                $memberShipCardsUsers[$detail->worker_id]['amount'] = $user_amount;
                                $memberShipCardsUsers[$detail->worker_id]['amountArr'][] = $user_amount;
                                $memberShipCardsUsers[$detail->worker_id]['detailsArr'][] = $memberShipCard->booking;
                                $memberShipCardsUsers[$detail->worker_id]['codesArr'][] = $memberShipCard->code;
                                $memberShipCardsUsers[$detail->worker_id]['code'] = $memberShipCard->code;
                                $memberShipCardsUsers[$detail->worker_id]['booking_id'] = $memberShipCard->booking->id;
                            }

                            if (isset($booking_with_discount[$memberShipCard->booking->id])) {
                                if (isset($booking_with_discount[$memberShipCard->booking->id][$detail->worker_id][$detail->service_id])) {
                                    $booking_with_discount[$memberShipCard->booking->id][$detail->worker_id][$detail->service_id] += $user_amount;
                                } else {
                                    $booking_with_discount[$memberShipCard->booking->id][$detail->worker_id][$detail->service_id] = $user_amount;
                                }
                            } else {
                                $booking_with_discount[$memberShipCard->booking->id][$detail->worker_id][$detail->service_id] = $user_amount;
                            }
                        }
                    }
                }
            }

            $temp_discount = UserUsedDiscount::with('booking', 'booking.details')->whereRaw('DATE(created_at)="' . $date . '"');
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
                $temp_discount->whereHas('booking', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                });
            }

            $discount = $temp_discount->get();
            if (!$discount->isEmpty()) {
                foreach ($discount as $static) {
                    $amount = $static->amount;
                    if (!empty($static->booking) && !empty($static->booking->details)) {
                        foreach ($static->booking->details as $detail) {
                            if ($static->type == "fixed") {
                                $user_amount = $amount;
                            } else {
                                $user_amount = ($detail->price * $amount) / 100;
                            }

                            if (isset($discountUsers[$detail->worker_id])) {
                                $discountUsers[$detail->worker_id]['amount'] += $user_amount;
                                $discountUsers[$detail->worker_id]['amountArr'][] = $user_amount;
                                $discountUsers[$detail->worker_id]['detailsArr'][] = $static->booking;
                                $discountUsers[$detail->worker_id]['codesArr'][] = $static->code;
                            } else {
                                $discountUsers[$detail->worker_id]['amount'] = $user_amount;
                                $discountUsers[$detail->worker_id]['booking_id'] = $static->booking->id;
                                $discountUsers[$detail->worker_id]['amountArr'][] = $user_amount;
                                $discountUsers[$detail->worker_id]['detailsArr'][] = $static->booking;
                                $discountUsers[$detail->worker_id]['codesArr'][] = $static->code;
                            }

                            if (isset($booking_with_discount[$static->booking->id])) {
                                if (isset($booking_with_discount[$static->booking->id][$detail->worker_id][$detail->service_id])) {
                                    $booking_with_discount[$static->booking->id][$detail->worker_id][$detail->service_id] += $user_amount;
                                } else {
                                    $booking_with_discount[$static->booking->id][$detail->worker_id][$detail->service_id] = $user_amount;
                                }
                            } else {
                                $booking_with_discount[$static->booking->id][$detail->worker_id][$detail->service_id] = $user_amount;
                            }
                        }
                    }
                }
            }

            if (!$report->isEmpty()) {
                foreach ($report as $value) {
                    $full_name = $value->full_name;
                    $mobile = $value->mobile;
                    $price = 0;
                    if (!empty($value->details)) {
                        foreach ($value->details as $detail) {
                            if (isset($result[$detail->worker_id])) {
                                $price = $detail->price;
                                $payment_type_select = $value->payment_type;
                                if (empty($value->payment_type)) {
                                    $payment_type_select = 'wallet';
                                }

                                $result[$detail->worker_id][$payment_type_select] += $price;
                                array_push($users_with_prices[$detail->worker_id], $price);

                                // Check if commission exists (not null) - allow 0 values
                                if ($detail->commission !== null && $detail->commission !== '') {
                                    // Calculate commission based on commission_type
                                    $commission_amount = $detail->commission;
                                    if ($detail->commission_type == 'percentage') {
                                        // If percentage, calculate: (price * commission) / 100
                                        $commission_amount = ($price * floatval($detail->commission)) / 100;
                                    } elseif ($detail->commission_type == 'fixed') {
                                        // If fixed, use commission value directly
                                        $commission_amount = floatval($detail->commission);
                                    } else {
                                        // Backward compatibility: if commission_type is null, assume percentage
                                        $commission_amount = ($price * floatval($detail->commission)) / 100;
                                    }
                                    array_push($users_with_commission[$detail->worker_id], $commission_amount);
                                }

                                if (!empty($detail->tip)) {
                                    array_push($users_with_tips[$detail->worker_id], $detail->tip);
                                }

                                $free_price = 0;
                                if (isset($booking_with_discount[$value->id][$detail->worker_id][$detail->service_id])) {
                                    $free_price = $booking_with_discount[$value->id][$detail->worker_id][$detail->service_id];
                                }

                                if ($detail->is_free == 1) {
                                    $free_price += $detail->price;
                                }

                                $payments_with_prices[$payment_type_select][$detail->worker_id][] = $price - $free_price;

                                if (isset($memberShipCardsUsers[$detail->worker_id]) && $value->id == $memberShipCardsUsers[$detail->worker_id]['booking_id'] && !in_array($detail->worker_id, $selected_memberShipCardsUsers)) {
                                    $temp = [];
                                    $temp['amount'] = $memberShipCardsUsers[$detail->worker_id]['amount'];
                                    $temp['details'] = $memberShipCardsUsers[$detail->worker_id]['amountArr'];
                                    $temp['detailsArr'] = $memberShipCardsUsers[$detail->worker_id]['detailsArr'];
                                    $temp['codesArr'] = $memberShipCardsUsers[$detail->worker_id]['codesArr'];
                                    $temp['type'] = "member_ship";
                                    $temp['code'] = $memberShipCardsUsers[$detail->worker_id]['code'];
                                    $temp['client_name'] = $full_name;
                                    $payments_with_prices["free"][$detail->worker_id][] = $temp;
                                    array_push($selected_memberShipCardsUsers, $detail->worker_id);
                                }

                                if (isset($discountUsers[$detail->worker_id]) && $value->id == $discountUsers[$detail->worker_id]['booking_id'] && !in_array($detail->worker_id, $selected_discountUsers)) {
                                    $temp = [];
                                    $temp['amount'] = $discountUsers[$detail->worker_id]['amount'];
                                    $temp['type'] = "discount_code";
                                    $temp['code'] = "";
                                    $temp['details'] = $discountUsers[$detail->worker_id]['amountArr'];
                                    $temp['detailsArr'] = $discountUsers[$detail->worker_id]['detailsArr'];
                                    $temp['codesArr'] = $discountUsers[$detail->worker_id]['codesArr'];
                                    $temp['client_name'] = $mobile;
                                    $payments_with_prices["free"][$detail->worker_id][] = $temp;
                                    array_push($selected_discountUsers, $detail->worker_id);
                                }

                                if ($detail->is_free == 1) {
                                    $temp = [];
                                    $temp['amount'] = $detail->price;
                                    $temp['type'] = "free_service";
                                    $temp['code'] = "";
                                    $temp['client_name'] = $mobile;
                                    $payments_with_prices["free"][$detail->worker_id][] = $temp;
                                }
                            }
                        }
                    }
                }
            }

            $temp_BuyProduct = BuyProduct::select('buy_products.*')
                ->with('details')
                ->join('workers', 'workers.id', '=', 'buy_products.worker_id')
                ->whereRaw('DATE(buy_products.created_at)="' . $date . '"');

            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
                if ($branch_id) {
                    $temp_BuyProduct = $temp_BuyProduct->where('workers.branch_id', $branch_id);
                }
            }

            $BuyProduct = $temp_BuyProduct->get();
            if (!empty($BuyProduct)) {
                foreach ($BuyProduct as $BuyProduct_item) {
                    $date = date('Y-m-d', strtotime($BuyProduct_item->created_at));
                    $temp = [
                        "amount" => 0,
                        "products" => []
                    ];

                    if (!$BuyProduct_item->details->isEmpty()) {
                        $orderPrice = 0;
                        foreach ($BuyProduct_item->details as $detail) {
                            // Ensure dynamic payment method key exists
                            if (!isset($product_details_prices[$BuyProduct_item->payment_type])) {
                                $product_details_prices[$BuyProduct_item->payment_type] = 0;
                            }

                            $product_price = $detail->product?->supply_price ?? $detail->product?->retail_price;
                            if (!empty($BuyProduct_item->discount)) {
                                $product_price -= ($product_price * $BuyProduct_item->discount) / 100;
                            }

                            $product_details_prices[$BuyProduct_item->payment_type] += $product_price;
                            $orderPrice += $product_price;
                            $temp["products"][] = $detail;
                        }

                        if (isset($result[$BuyProduct_item->sales_worker_id][$BuyProduct_item->payment_type])) {
                            $result[$BuyProduct_item->sales_worker_id][$BuyProduct_item->payment_type] += $orderPrice;
                            $temp["amount"] = $orderPrice;
                            $payments_with_prices[$BuyProduct_item->payment_type][$BuyProduct_item->sales_worker_id][] = $temp;
                        }
                    }

                    if (isset($users_with_commission[$BuyProduct_item->worker_id])) {
                        array_push($users_with_commission[$BuyProduct_item->worker_id], $BuyProduct_item->commission);
                    }

                    if (isset($users_with_tips[$BuyProduct_item->worker_id])) {
                        array_push($users_with_tips[$BuyProduct_item->worker_id], $BuyProduct_item->tip);
                    }
                }
            }

            $temp_get_wallets = UserWallet::whereRaw('DATE(users_wallets.created_at)="' . $date . '"');
            if (get_user_role() == 1 || $selected_branch) {
                $branch_id = $selected_branch ? $selected_branch : auth('center_user')->user()->branch_id;
                $temp_get_wallets = $temp_get_wallets->join('users', 'users.id', '=', 'users_wallets.user_id')->where('users.branch_id', $branch_id);
            }

            $get_wallets = $temp_get_wallets->get();
            if (!$get_wallets->isEmpty()) {
                foreach ($get_wallets as $get_wallet) {
                    // Ensure dynamic wallet payment method key exists
                    if (!isset($wallet_details_prices[$get_wallet->wallet_type])) {
                        $wallet_details_prices[$get_wallet->wallet_type] = 0;
                    }
                    $wallet_details_prices[$get_wallet->wallet_type] += $get_wallet->invoiced_amount;

                    if (isset($users_with_commission[$get_wallet->worker_id])) {
                        array_push($users_with_commission[$get_wallet->worker_id], $get_wallet->commission);
                    }

                    if (isset($users_with_tips[$get_wallet->worker_id])) {
                        array_push($users_with_tips[$get_wallet->worker_id], $get_wallet->tip);
                    }
                }
            }
        }

        $formattedDate = Carbon::now();
        if ($date) {
            $formattedDate = Carbon::createFromFormat("Y-m-d", $date);
        }
        $vacationsWorkerIds = Vacation::whereRaw('DATE(day)=\'' . $formattedDate->format("Y-m-d") . '\'')->get()->pluck("worker_id")->toArray();

        $template = (string)view('CenterUser.SubViews.Report.template.daily_report', compact(
            'date',
            'users',
            'firstusers',
            'secondusers',
            'restusers',
            'users_with_prices',
            'payments_with_prices',
            'payments_type',
            'product_details_prices',
            'wallet_details_prices',
            'users_with_commission',
            'users_with_tips',
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

            $pdf = Pdf::loadview('CenterUser.SubViews.Report.pdf.daily_report', compact(
                'template',
            ), [], $options);
            return $pdf->stream('daily_report.pdf');
        }

        $temp_branches = Branch::query();
        if (get_user_role() != 1) {
            $temp_branches->where('id', auth('center_user')->user()->branch_id);
        }
        $branches = $temp_branches->get();
        return view('CenterUser.SubViews.Report.daily_report', compact(
            'date',
            'template',
            'branches',
            'selected_branch',
            'request',
        ));
    }
}
