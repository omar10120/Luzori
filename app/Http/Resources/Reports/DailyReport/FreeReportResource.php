<?php

namespace App\Http\Resources\Reports\DailyReport;

use App\Models\Worker;
use Illuminate\Http\Resources\Json\JsonResource;

class FreeReportResource extends JsonResource
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
        foreach ($this->resource as $id => $item) {
            $worker = Worker::find($id);
            $total = 0;
            foreach ($item as $i) {
                $total += $i['amount'];
            }
            $res[] = [
                'id' => $id,
                'name' => $worker->name,
                'total' => $total,
                'items' => FreeItemReportResource::collection($item),
            ];
        }
        return $res;
    }
}
