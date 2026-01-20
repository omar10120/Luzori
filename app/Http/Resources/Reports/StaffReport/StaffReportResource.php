<?php

namespace App\Http\Resources\Reports\StaffReport;

use App\Http\Resources\Reports\DailyReport\UserReportResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffReportResource extends JsonResource
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
        $res['result'] = new StaffResultReportResource($this['result']);
        $res['total_prices'] = new TotalResultReportResource($this['result']['price']);
        $res['total_tips'] = new TotalResultReportResource($this['result']['tips']);

        $prices = $res['total_prices']->toArray(null);
        $tips = $res['total_tips']->toArray(null);
        $total = [];
        foreach ($prices as $item) {
            foreach ($tips as $tip) {
                if ($item['worker_id'] == $tip['worker_id']) {
                    $total[] = [
                        'worker_id' => $item['worker_id'],
                        'total' => $item['total'] + $tip['total']
                    ];
                }
            }
        }
        $res['total'] = $total;

        $total_of_total = 0;
        foreach ($total as $t) {
            $total_of_total += $t['total'];
        }
        $res['total_of_total'] = $total_of_total;
        $res['vacationsWorkerIds'] = $this['vacationsWorkerIds'];
        return $res;
    }
}
