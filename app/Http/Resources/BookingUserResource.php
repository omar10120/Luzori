<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingUserResource extends JsonResource
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
        $res['services'] = BookingServiceResource::collection($this->services->groupBy('service_id'));
        $res['wallets'] = BookingWalletResource::collection($this->wallets);
        $res['memberships'] = BookingMembershipResource::collection($this->memberships);
        return $res;
    }
}
