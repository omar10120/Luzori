<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
        $res['city'] = $this->translate(app()->getLocale())->city;
        $res['address'] = $this->translate(app()->getLocale())->address;
        $res['longitude'] = $this->longitude;
        $res['latitude'] = $this->latitude;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
