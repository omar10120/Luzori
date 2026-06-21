<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceSettingsResource extends JsonResource
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
        $res['phone_number_1'] = $this->phone_number_1;
        $res['phone_number_2'] = $this->phone_number_2;
        $res['phone_number_3'] = $this->phone_number_3;
        $res['emirate'] = $this->emirate;
        $res['tax_number'] = $this->tax_number;
        return $res;
    }
}
