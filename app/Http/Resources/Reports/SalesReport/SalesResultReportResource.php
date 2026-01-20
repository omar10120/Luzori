<?php

namespace App\Http\Resources\Reports\SalesReport;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesResultReportResource extends JsonResource
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
        
        foreach ($this->resource as $date => $item) {
            $row = ['date' => $date];
            
            // Add all payment methods dynamically
            foreach ($paymentMethods as $method) {
                $row[$method] = $item[$method] ?? 0;
            }
            
            $row['commission'] = $item['commission'];
            
            // Calculate total without free dynamically
            $total_without_free = $item['commission'];
            foreach ($paymentMethods as $method) {
                if ($method !== 'free' && $method !== 'wallet') {
                    $total_without_free += $item[$method] ?? 0;
                }
            }
            $row['total_without_free'] = $total_without_free;
            
            $res[] = $row;
        }
        return $res;
    }
}
