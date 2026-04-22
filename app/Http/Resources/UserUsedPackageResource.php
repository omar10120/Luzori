<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserUsedPackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_package_id' => $this->user_package_id,
            'booking_id' => $this->booking_id,
            'service_id' => $this->service_id,
            'service_name' => $this->service ? ($this->service->translation?->name ?? $this->service->name) : null,
            'is_free' => (bool) $this->is_free,
            'created_at' => $this->created_at ? ($this->created_at instanceof \Carbon\Carbon ? $this->created_at->toDateTimeString() : $this->created_at) : null,
        ];
    }
}
