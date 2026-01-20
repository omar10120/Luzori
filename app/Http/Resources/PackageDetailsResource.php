<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageDetailsResource extends JsonResource
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
        ];
        $res['en'] = [
            'name' => $this->translate('en')->name,
        ];
        $res['ServicePaid'] = PackageServiceResource::collection($this->packageServicePaid);
        $res['ServiceFree'] = PackageServiceResource::collection($this->packageServiceFree);
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
