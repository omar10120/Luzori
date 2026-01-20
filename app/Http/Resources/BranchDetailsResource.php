<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchDetailsResource extends JsonResource
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
            'city' => $this->translate('ar')->city,
            'address' => $this->translate('ar')->address,
        ];
        $res['en'] = [
            'name' => $this->translate('en')->name,
            'city' => $this->translate('en')->city,
            'address' => $this->translate('en')->address,
        ];
        $res['longitude'] = $this->longitude;
        $res['latitude'] = $this->latitude;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
