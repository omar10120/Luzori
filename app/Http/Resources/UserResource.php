<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
        $res['first_name'] = $this->first_name;
        $res['last_name'] = $this->last_name;
        $res['email'] = $this->email;
        $res['country_code'] = $this->country_code;
        $res['phone'] = $this->phone;
        $res['wallet'] = $this->wallet;
        $res['image'] = $this->image;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
