<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res['email'] = $this->email;
        $res['phone'] = $this->phone;
        $res['facebook'] = $this->facebook;
        $res['linkedin'] = $this->facebook;
        $res['instagram'] = $this->instagram;
        $res['twitter'] = $this->instagram;
        $res['whatsapp'] = $this->whatsapp;
        $res['youtube'] = $this->youtube;
        return $res;
    }
}
