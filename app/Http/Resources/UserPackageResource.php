<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user ? $this->user->name : null,
            'package_id' => $this->package_id,
            'package_name' => $this->package ? $this->package->name : null,
            'package_details' => new PackageResource($this->whenLoaded('package')),
            'price' => (double) $this->price,
            'status' => $this->status,
            'package_type' => $this->package_type,
            'created_at' => ($this->created_at instanceof \Carbon\Carbon) ? $this->created_at->toDateTimeString() : $this->created_at,
            'used_packages' => UserUsedPackageResource::collection($this->whenLoaded('usedPackages')),
        ];
    }
}
