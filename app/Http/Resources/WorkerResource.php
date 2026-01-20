<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
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
        $res['email'] = $this->email;
        $res['country_code'] = $this->country_code;
        $res['phone'] = $this->phone;
        $res['has_commission'] = $this->has_commission;
        $res['image'] = $this->image;
        $res['branch'] = BranchResource::make($this->branch);
        $res['shift'] = ShiftResource::make($this->shift);

        $services = collect();
        foreach ($this->services as $service) {
            if ($service->service) {
                $services->push(ServiceResource::make($service->service));
            }
        }
        $res['services'] = $services;
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
