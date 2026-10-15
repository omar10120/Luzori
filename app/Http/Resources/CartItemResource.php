<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'center_id' => (int) $this->center_id,
            'center' => $this->whenLoaded('center', function () {
                return [
                    'id' => $this->center->id,
                    'name' => $this->center->name,
                    'domain' => $this->center->domain,
                    // 'logo' => $this->center->logo ? url('storage/' . $this->center->logo) : null,
                ];
            }),
            'branch_id' => $this->branch_id ? (int) $this->branch_id : null,
            'item_type' => $this->item_type,
            'item_id' => (int) $this->item_id,
            'qty' => $this->qty !== null ? (int) $this->qty : null,
            'name' => $this->name,
            'sku' => $this->sku,
            'image_url' => $this->image_url,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,
            'request_payload' => $this->request_payload,
            'booking' => $this->item_type === 'booking' ? [
                'booking_number' => $this->booking_number,
                'worker_id' => $this->worker_id,
                'booking_date' => $this->booking_date?->format('Y-m-d'),
                'from_time' => $this->from_time,
                'to_time' => $this->to_time,
                'user_package_ids' => $this->user_package_ids,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
