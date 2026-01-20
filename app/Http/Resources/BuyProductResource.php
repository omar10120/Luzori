<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuyProductResource extends JsonResource
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
        $res['payment_type'] = $this->payment_type;
        $res['commission'] = $this->commission;
        $res['discount'] = $this->discount;
        $res['products'] = $this->details->pluck('product.name');
        $res['sales_worker'] = WorkerResource::make($this->sales_worker);
        $res['worker'] = WorkerResource::make($this->worker);
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
