<?php

namespace App\Http\Resources;

use App\Enums\SettingEnum;
use App\Models\BuyProductDetail;
use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintBuyProductResource extends JsonResource
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
        $res['date'] = $this->created_at;
        $res['served_by'] = $this->created_by_user->name ?? '-';

        $total = 0;
        $res['products'] = BuyProductDetailResource::collection($this->details);
        foreach ($this->details as $detail) {
            $buyProductDetail = BuyProductDetail::find($detail->id);
            $total += $buyProductDetail->price;
        }

        $res['vat'] = number_format($total * 0.05, 2, '.', '');
        $res['subTotal'] = number_format($total - $total * 0.05, 2, '.', '');
        $res['total'] = number_format($total, 2, '.', '');
        return $res;
    }
}
