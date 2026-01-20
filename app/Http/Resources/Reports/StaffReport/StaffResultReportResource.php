<?php

namespace App\Http\Resources\Reports\StaffReport;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResultReportResource extends JsonResource
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
        foreach ($this->resource as $type => $day) {
            if ($type == 'price') {
                foreach ($day as $date => $d) {
                    foreach ($d as $worker_id => $price) {
                        $res[$date][] = [
                            'id' => $worker_id,
                            'price' => $price,
                        ];
                    }
                }
            }
            if ($type == 'tips') {
                foreach ($day as $date => $d) {
                    foreach ($d as $worker_id => $price) {
                        $exists = false;
                        foreach ($res[$date] as &$lastDate) {
                            if ($lastDate['id'] == $worker_id) {
                                $lastDate['price'] = $lastDate['price'] + $price;
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $res[$date][] = [
                                'id' => $worker_id,
                                'price' => $price,
                            ];
                        }
                    }
                }
            }
        }
        return $res;
    }
}
