<?php

namespace App\Http\Resources\Reports\SalesReport;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesResultReportResource extends JsonResource
{
    public array $usedWalletAmountKeys = [];

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
        $usedWalletAmountKeys = $this->usedWalletAmountKeys;

        foreach ($this->resource as $date => $item) {
            $row = ['date' => $date];

            foreach ($paymentMethods as $method) {
                $row[$method] = $item[$method] ?? 0;
            }

            $row['commission'] = $item['commission'];

            $total_without_free = $item['commission'];
            foreach ($paymentMethods as $method) {
                if ($method !== 'free' && $method !== 'wallet' && !in_array($method, $usedWalletAmountKeys, true)) {
                    $total_without_free += $item[$method] ?? 0;
                }
            }
            foreach ($item as $key => $value) {
                if (
                    !in_array($key, $paymentMethods, true)
                    && $key !== 'commission'
                    && $key !== 'free'
                    && $key !== 'wallet'
                    && !in_array($key, $usedWalletAmountKeys, true)
                ) {
                    $total_without_free += $value;
                }
            }
            $row['total_without_free'] = $total_without_free;

            $res[] = $row;
        }
        return $res;
    }
}
