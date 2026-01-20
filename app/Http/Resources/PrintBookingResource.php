<?php

namespace App\Http\Resources;

use App\Enums\SettingEnum;
use App\Models\BookingDetail;
use App\Models\Setting;
use App\Models\UserUsedCard;
use App\Models\UserUsedDiscount;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintBookingResource extends JsonResource
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

        $service_prices = 0;
        $res['services'] = BookingDetailResource::collection($this->details);
        foreach ($this->details as $detail) {
            $bookingDetail = BookingDetail::find($detail->id);
            $service_prices += $bookingDetail->price;
        }

        if ($this->wallet) {
            $res['wallet'] = WalletResource::make($this->wallet);
        }

        $discount_amount = 0;
        if (UserUsedDiscount::where('booking_id', $this->id)->exists()) {
            $discount = UserUsedDiscount::with(['discount_code'])->where('booking_id', $this->id)->first();
            $res['discount_code'] = DiscountResource::make($discount->discount_code);
            $discount_amount = $discount->discount_code->amount;
        }

        $card_amount = 0;
        if (UserUsedCard::where('booking_id', $this->id)->exists()) {
            $card = UserUsedCard::with(['membership_card' => function ($q) {
                $q->withTrashed();
            }])->where('booking_id', $this->id)->first();
            $res['member_ship_card'] = MembershipResource::make($card->membership_card);
            $card_amount = $card->amount;
        }

        $total = $service_prices - $discount_amount - $card_amount;

        $res['vat'] = number_format($total * 0.05, 2, '.', '');
        $res['subTotal'] = number_format($total - $total * 0.05, 2, '.', '');
        $res['total'] = number_format($total, 2, '.', '');
        return $res;
    }
}
