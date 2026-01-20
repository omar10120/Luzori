<?php

namespace App\Http\Resources\Reports\CommissionReport;

use Illuminate\Http\Resources\Json\JsonResource;

class CommissionTotalReportResource extends JsonResource
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
        foreach ($this->resource as $worker_id => $price) {
            $res[] = [
                'id' => $worker_id,
                'price' => $price,
            ];
        }
        return $res;
    }
}
