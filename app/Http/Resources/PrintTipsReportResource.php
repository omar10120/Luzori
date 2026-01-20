<?php

namespace App\Http\Resources;

use App\Enums\SettingEnum;
use App\Models\Setting;
use App\Models\Worker;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintTipsReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $total = 0;
        foreach ($this->resource['tips'] as $day) {
            foreach ($day as $price) {
                $total += $price;
            }
        }

        $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);

        $invoiceInfo = Setting::where('key', SettingEnum::invoice_info->value)->first()->value ?? '';

        $res['invoiceInfo'] = $invoiceInfo;
        $res['date'] = $request->year . '-' . $request->month . '-01 To ' . $request->year . '-' . $request->month . '-' . $days;
        $res['payment_method'] = 'Tips Visa';
        $res['worker_name'] = Worker::find($request->worker_id)->first()->name;
        $res['total'] = $total;
        return $res;
    }
}
