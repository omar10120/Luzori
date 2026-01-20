<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
        $res['text'] = $this->translate(app()->getLocale())->text;
        $res['price'] = $this->retail_price;
        $res['image'] = $this->image;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
