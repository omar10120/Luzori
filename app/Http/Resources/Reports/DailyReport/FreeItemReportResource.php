<?php

namespace App\Http\Resources\Reports\DailyReport;

use Illuminate\Http\Resources\Json\JsonResource;

class FreeItemReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res['total'] = $this['amount'];
        $res['type'] = $this['type'];
        $res['client_name'] = $this['client_name'];
        if (isset($this['details'])) {
            $items = [];
            foreach ($this['details'] as $key => $detail) {
                $item = [];
                $item['discount'] = $detail;
                $item['full_name'] = $this['detailsArr'][$key]['full_name'];
                $item['mobile'] = $this['detailsArr'][$key]['mobile'];
                $item['code'] = $this['codesArr'][$key];
                array_push($items, $item);
            }
            $res['items'] = $items;
        }
        return $res;
    }
}
