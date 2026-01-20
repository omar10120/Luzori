<?php

namespace App\Http\Resources\Reports\TipsReport;

use App\Http\Resources\Reports\DailyReport\UserReportResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TipsReportResource extends JsonResource
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
        $res['result'] = new TipsResultReportResource($this['result']);
        $res['total'] = new TotalResultReportResource($this['result']);
        $res['total_with_tips'] = new TotalWithTipsResultReportResource($this['result']);
        $res['tips'] = $this['tips'];

        $total = $res['total']->toArray(null);
        $sum_total = 0;
        foreach ($total as $t) {
            $sum_total += $t['total'];
        }
        $res['sum_total'] = $sum_total;
        $res['sum_total_with_tips'] = $sum_total - ($sum_total * $this['tips'] / 100);
        $res['vacationsWorkerIds'] = $this['vacationsWorkerIds'];
        return $res;
    }
}
