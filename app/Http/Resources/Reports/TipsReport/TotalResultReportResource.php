<?php

namespace App\Http\Resources\Reports\TipsReport;

use Illuminate\Http\Resources\Json\JsonResource;

class TotalResultReportResource extends JsonResource
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
        foreach ($this->resource as $date => $day) {
            foreach ($day as $worker_id => $price) {
                $res[$worker_id] = [
                    'worker_id' => $worker_id,
                    'total' => 0
                ];
            }
            break;
        }
        foreach ($this->resource as $date => $day) {
            foreach ($day as $worker_id => $price) {
                $res[$worker_id] = [
                    'worker_id' => $worker_id,
                    'total' => $res[$worker_id]['total'] + $price
                ];
            }
        }
        return $res;
    }
}
