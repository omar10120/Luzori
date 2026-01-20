<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
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
        $res['domain'] = $this->domain;
        $res['email'] = $this->email;
        $res['phone'] = $this->phone;
        $res['image'] = $this->image;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
 