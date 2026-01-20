<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailResource extends JsonResource
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
        $res['service_name'] = $this->service->name;
        $res['date_and_time'] = $this->_date . ' ' . $this->from_time . ' ' . $this->to_time;
        
        if ($this->is_free) {
            $res['price'] = 'Free';
        } else {
            $res['price'] = $this->price;
        }
        return $res;
    }
}
