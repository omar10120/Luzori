<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingWithTipsResource extends JsonResource
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
        $res['date'] = $this->details?->first()?->_date;
        $res['worker_name'] = $this->details?->first()?->worker?->name ?? '-';
        $res['tip'] = $this->details?->first()?->tip;
        return $res;
    }
}
