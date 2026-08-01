<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res['id'] = $this->id;
        $res['name'] = $this->name;
        $res['domain'] = $this->domain;
        $res['status'] = $this->status;
    //  $res['email'] = $this->email;
    //  $res['phone'] = $this->phone;

        $res['logo'] = $this->getFirstMediaUrl('Center') ?: asset('assets/img/avatars/1.png');
        // $res['primary_image'] = $this->getFirstMediaUrl('PrimaryImage') ?: asset('assets/img/avatars/1.png');
        $res['primary_images'] = $this->getMedia('PrimaryImage')->map(function ($media) {
            return $media->getUrl();
        })->toArray();
        $res['rate'] = $this->rate;
        $res['global_categories'] = GlobalCategoryResource::collection($this->whenLoaded('globalCategories'));
        $res['created_at'] = $this->created_at;

        // Include nested data if provided via attributes or explicitly loaded
        if (isset($this->branches)) {
            $res['branches'] = BranchResource::collection($this->branches);
        }
        if (isset($this->categories)) {
            $res['categories'] = CategoryServiceResource::collection($this->categories);
        }

        if (isset($this->services)) {
            $res['services'] = ServiceResource::collection($this->services);
        }
        if (isset($this->packages)) {
            $res['packages'] = PackageResource::collection($this->packages);
        }
        if (isset($this->user_packages)) {
            $res['user_packages'] = UserPackageResource::collection($this->user_packages);
        }

        if (isset($this->user_used_packages)) {
            $res['user_used_packages'] = UserUsedPackageResource::collection($this->user_used_packages);
        }

        if (isset($this->workers)) {
            $res['workers'] = WorkerResource::collection($this->workers);
        }

        if (isset($this->about_us)) {
            $res['about_us'] = $this->about_us
                ? PageResource::make($this->about_us)
                : null;
        }
        if (isset($this->infos)) {
            $res['infos'] = InfoResource::collection($this->infos);
        }

        return $res;
    }
}
 