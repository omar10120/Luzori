<?php

namespace App\Http\Resources\Reports\CommissionReport;

use App\Http\Resources\Reports\DailyReport\UserReportResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionReportResource extends JsonResource
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
        $res['result'] = new CommissionResultReportResource($this['result']);
        $res['total'] = new CommissionTotalReportResource($this['users_with_totals']);
        $res['total_of_total'] = array_sum($this['users_with_totals']);
        return $res;
    }
}
