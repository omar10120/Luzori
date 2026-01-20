<?php

namespace App\Http\Resources;

use App\Enums\SettingEnum;
use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintBookingWithTipsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $invoiceInfo = Setting::where('key', SettingEnum::invoice_info->value)->first()->value ?? '';

        $res['id'] = $this->id;
        $res['invoiceInfo'] = $invoiceInfo;
        $res['receipt'] = $this->id;
        $res['date'] = $this->booking_date;
        $res['served_by'] = $this->createdBy->name ?? '-';
        $res['payment_method'] = $this->payment_type;
        $res['customer_name'] = $this->full_name;
        $res['customer_mobile'] = $this->mobile;
        $res['date_and_time'] = $this->details->first()->_date;
        $res['tips'] = $this->details->first()->tip;
        $res['total'] = number_format(0, 2, '.', '');
        return $res;
    }
}
