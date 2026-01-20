<?php

namespace App\Http\Resources\Reports\SalesReport;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res['result'] = new SalesResultReportResource($this['result']);

        $total = [];
        $paymentMethods = get_payment_method_names();
        $dynamicTotals = [];
        $commission = 0;
        $total_without_free = 0;
        
        // Initialize dynamic totals for all payment methods
        foreach ($paymentMethods as $method) {
            $dynamicTotals[$method] = 0;
        }
        
        // Check if center has permission to view transfer_bank
        
        foreach ($res['result'] as $result) {
            foreach ($result as $item) {
                // Process all payment methods dynamically
                foreach ($paymentMethods as $method) {
                    if (isset($item[$method])) {
                        $dynamicTotals[$method] += $item[$method];
                    }
                }
                
                $commission += $item['commission'];
                $total_without_free += $item['total_without_free'] ?? 0;
            }
        }
        
        // Set totals from dynamic calculation
        foreach ($dynamicTotals as $method => $value) {
            $total[$method] = $value;
        }
        
        $total['commission'] = $commission;
        
        // Calculate total without free dynamically
        $total_without_free_calculation = $commission;
        foreach ($paymentMethods as $method) {
            if ($method !== 'free' && $method !== 'wallet') {
                $total_without_free_calculation += $dynamicTotals[$method];
            }
        }
        
        $total['total_without_free'] = $total_without_free_calculation;
        $res['total'] = $total;
        $res['total_of_total'] = $total['total_without_free'] + ($dynamicTotals['free'] ?? 0) + ($dynamicTotals['wallet'] ?? 0);
        return $res;
    }
}
