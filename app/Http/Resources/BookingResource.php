<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
        $res['branch_name'] = $this->branch?->translate(app()->getLocale())?->name ?? '-';
        $res['services'] = $this->details->pluck('service.name');
        $res['full_name'] = $this->full_name;
        $res['mobile'] = $this->mobile;
        $res['payment_type'] = $this->payment_type;
        $res['booking_date'] = $this->booking_date;
        $res['created_by'] = $this->createdBy->name ?? '-';
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
