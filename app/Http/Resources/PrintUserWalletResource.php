<?php

namespace App\Http\Resources;

use App\Enums\SettingEnum;
use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintUserWalletResource extends JsonResource
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
        $res['wallet_id'] = $this->id;
        $res['date'] = $this->created_at;
        $res['customer_name'] = $this->user->name ?? '-';
        $res['customer_phone'] = $this->user->full_phone ?? '-';
        $res['customer_email'] = $this->user->email ?? '-';
        $res['vat'] = get_num_format($this->invoiced_amount * 0.05);
        $res['subTotal'] = get_num_format($this->invoiced_amount - $this->invoiced_amount * 0.05);
        $res['total'] = get_num_format($this->invoiced_amount);
        return $res;
    }
}
