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
        $res['status'] = $this->status;
    //  $res['email'] = $this->email;
    //  $res['phone'] = $this->phone;

        $res['logo'] = $this->getFirstMediaUrl('Center') ?: asset('assets/img/avatars/1.png');
        // $res['primary_image'] = $this->getFirstMediaUrl('PrimaryImage') ?: asset('assets/img/avatars/1.png');
        $res['primary_images'] = $this->getMedia('PrimaryImage')->map(function ($media) {
            return $media->getUrl();
        })->toArray();
        $res['rate'] = $this->rate;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
 