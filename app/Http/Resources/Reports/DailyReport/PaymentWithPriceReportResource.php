<?php

namespace App\Http\Resources\Reports\DailyReport;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentWithPriceReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res = [];
        $paymentMethods = get_payment_method_names();
        
        foreach ($this->resource as $type => $item) {
            // Handle dynamic payment methods
            if (in_array($type, $paymentMethods)) {
                $res[$type] = new UserWithPriceReportResource($item);
            }
            // Handle special cases
            elseif ($type == 'wallet') {
                $res['wallet'] = new UserWithPriceReportResource($item);
            }
            elseif ($type == 'tips_visa') {
                $res['tips_visa'] = new UserWithPriceReportResource($item);
            }
            elseif ($type == 'free') {
                $res['free'] = new FreeReportResource($item);
            }
        }
        return $res;
    }
}
