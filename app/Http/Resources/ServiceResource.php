<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'rooms_no' => $this->rooms_no,
            'max_time' => $this->max_time,
            'extra_time' => $this->extra_time,
            'is_top' => $this->is_top,
            'image' => $this->getFirstMediaUrl('Service') ?: asset('assets/img/avatars/1.png'),
        ];
    }
}
