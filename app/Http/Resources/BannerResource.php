<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request): array
    {
        $target = match ($this->link_type) {
            'external' => [
                'type' => 'external',
                'url' => $this->external_url,
            ],
            'category' => [
                'type' => 'category',
                'id' => $this->global_category_id,
                'name' => $this->globalCategory?->name,
                'slug' => $this->globalCategory?->slug,
            ],
            'center' => [
                'type' => 'center',
                'id' => $this->center_id,
                'name' => $this->center?->name,
            ],
            'service' => [
                'type' => 'service',
                'id' => $this->service_id,
                'center_id' => $this->center_id,
                'center_name' => $this->center?->name,
            ],
            default => null,
        };

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'placement_type' => $this->placement_type,
            'link_type' => $this->link_type,
            'target' => $target,
            'sort_order' => $this->sort_order,
        ];
    }
}
