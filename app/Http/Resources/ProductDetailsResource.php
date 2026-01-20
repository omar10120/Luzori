<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
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
        $res['ar'] = [
            'name' => $this->translate('ar')->name,
            'text' => $this->translate('ar')->text,
        ];
        $res['en'] = [
            'name' => $this->translate('en')->name,
            'text' => $this->translate('en')->text,
        ];
        $res['price'] = $this->retail_price;
        $res['image'] = $this->image;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
