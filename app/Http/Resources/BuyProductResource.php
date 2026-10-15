<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuyProductResource extends JsonResource
{
    public function toArray($request)
    {
        $details = $this->relationLoaded('details') ? $this->details : collect();

        $products = $details->map(function ($detail) {
            $product = $detail->product;

            return [
                'id'         => $detail->id,
                'product_id' => (int) $detail->product_id,
                'name'       => $product?->translation->name ?? $product?->name,
                'price'      => (float) $detail->price,
                'image_url'  => $product ? ($product->getFirstMediaUrl('Product') ?: null) : null,
            ];
        })->values();

        // qty + totals come from the linked sale_items row
        $saleItem = $this->relationLoaded('saleItem') ? $this->saleItem : null;

        return [
            'id'             => $this->id,
            'payment_type'   => $this->payment_type,
            'commission'     => $this->commission !== null ? (float) $this->commission : null,
            'discount'       => $this->discount !== null ? (float) $this->discount : null,
            'global_user_id' => $this->global_user_id ? (int) $this->global_user_id : null,
            'sale_id'        => $this->sale_id ? (int) $this->sale_id : null,

            // ← from sale_items
            'qty'            => (int) ($saleItem->quantity ?? 0),
            'unit_price'     => $saleItem ? (float) $saleItem->price : null,
            'subtotal'       => $saleItem ? (float) $saleItem->subtotal : (float) $details->sum('price'),

            // derived from details (kept for backwards compat)
            'total'          => (float) $details->sum('price'),
            'items_count'    => $details->count(),

            'products'       => $products,
            'product_names'  => $products->pluck('name')->filter()->values(),

            'sales_worker'   => $this->sales_worker ? WorkerResource::make($this->sales_worker) : null,
            'worker'         => $this->worker ? WorkerResource::make($this->worker) : null,
            'center'         => $this->when(isset($this->center), $this->center),
            'created_at'     => $this->created_at,
        ];
    }
}