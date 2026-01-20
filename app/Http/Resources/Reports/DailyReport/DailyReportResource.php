<?php

namespace App\Http\Resources\Reports\DailyReport;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res['firstusers'] = UserReportResource::collection($this['firstusers']);
        $res['secondusers'] = UserReportResource::collection($this['secondusers']);
        $res['restusers'] = UserReportResource::collection($this['restusers']);
        $res['users_with_prices'] = new UserWithPriceReportResource($this['users_with_prices']);
        $res['payments_with_prices'] = new PaymentWithPriceReportResource($this['payments_with_prices']);
        // $res['payments_type'] = $this['payments_type'];
        // $res['product_details_prices'] = $this['product_details_prices'];
        // $res['wallet_details_prices'] = $this['wallet_details_prices'];
        $res['users_with_commission'] = new UserWithPriceReportResource($this['users_with_commission']);
        $res['users_with_tips'] = new UserWithPriceReportResource($this['users_with_tips']);
        $res['vacationsWorkerIds'] = $this['vacationsWorkerIds'];

        $total = [];
        $paymentMethods = get_payment_method_names();
        
        foreach ($res['payments_with_prices'] as $paymentWithPrice) {
            foreach ($paymentWithPrice as $key => $item) {
                if (in_array($key, $paymentMethods)) {
                    $sum = 0;
                    foreach ($item as $prices) {
                        if (is_array($prices)) {
                            $sum += array_sum($prices);
                        } else {
                            $sum += $prices;
                        }
                    }
                    $total[$key] = $sum;
                }
            }
        }

        $sum = 0;
        foreach ($res['users_with_commission'] as $usersWithCommission) {
            foreach ($usersWithCommission as $prices) {
                $sum += array_sum($prices);
            }
        }
        $total['commission'] = $sum;

        $sum = 0;
        foreach ($res['users_with_tips'] as $usersWithTips) {
            foreach ($usersWithTips as $prices) {
                $sum += array_sum($prices);
            }
        }
        $total['tips'] = $sum;

        // Dynamically process all payment methods from product_details_prices
        if (isset($this['product_details_prices'])) {
            foreach ($this['product_details_prices'] as $key => $value) {
                if ($value > 0) {
                    $total[$key] = $value;
                }
            }
        }
        
        // Dynamically process all payment methods from wallet_details_prices (keys already include any suffix like _cp)
        if (isset($this['wallet_details_prices'])) {
            foreach ($this['wallet_details_prices'] as $key => $value) {
                if ($value > 0) {
                    $total[$key] = $value;
                }
            }
        }
        
        $total['total'] = array_sum($total);
        $res['total'] = $total;
        return $res;
    }
}
