<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res['id'] = $this->id;
        $res['name'] = $this->name;
        $res['start_time'] = $this->start_time;
        $res['end_time'] = $this->end_time;
        $res['break_start'] = $this->break_start;
        $res['break_end'] = $this->break_end;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
