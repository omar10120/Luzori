<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
        $res['name'] = $this->translate(app()->getLocale())->name;
        $res['rooms_no'] = $this->rooms_no;
        $res['free_book'] = $this->free_book;
        $res['price'] = $this->price;
        $res['is_top'] = $this->is_top;
        $res['has_commission'] = $this->has_commission;
        $res['image'] = $this->image;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
