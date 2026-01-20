<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
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
        $res['code'] = $this->code;
        $res['type'] = $this->type;
        $res['using_type'] = $this->using_type;
        $res['amount'] = $this->amount;
        $res['benefit_numbers'] = $this->benefit_numbers;
        $res['start_at'] = $this->start_at;
        $res['end_at'] = $this->end_at;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
