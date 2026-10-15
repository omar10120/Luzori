<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuyProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $details = $this->relationLoaded('details') ? $this->details : collect();

        $products = $details->map(function ($detail) {
            $product = $detail->product;

            return [
                'id' => $detail->id,
                'product_id' => (int) $detail->product_id,
                'name' => $product?->name,
                'price' => (float) $detail->price,
                'image_url' => $product ? ($product->getFirstMediaUrl('Product') ?: null) : null,
            ];
        })->values();

        return [
            'id' => $this->id,
            'payment_type' => $this->payment_type,
            'commission' => $this->commission !== null ? (float) $this->commission : null,
            'discount' => $this->discount !== null ? (float) $this->discount : null,
            'global_user_id' => $this->global_user_id ? (int) $this->global_user_id : null,
            'sale_id' => $this->sale_id ? (int) $this->sale_id : null,
            'qty' => $products->count(),
            'total' => (float) $details->sum('price'),
            'products' => $products,
            'product_names' => $products->pluck('name')->filter()->values(),
            'sales_worker' => WorkerResource::make($this->sales_worker),
            'worker' => WorkerResource::make($this->worker),
            'center' => $this->when(isset($this->center), $this->center),
            'created_at' => $this->created_at,
        ];
    }
}
