<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
        $res['ServicePaid'] = PackageServiceResource::collection($this->packageServicePaid);
        $res['ServiceFree'] = PackageServiceResource::collection($this->packageServiceFree);
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
