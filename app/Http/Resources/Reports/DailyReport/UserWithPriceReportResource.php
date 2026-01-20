<?php

namespace App\Http\Resources\Reports\DailyReport;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWithPriceReportResource extends JsonResource
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
        foreach ($this->resource as $id => $prices) {
            $res[] = [
                'id' => $id,
                'prices' => $prices,
                'total' => array_sum($prices),
            ];
        }
        return $res;
    }
}
