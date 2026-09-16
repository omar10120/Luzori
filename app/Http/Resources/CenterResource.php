<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Only outputs relations that are already loaded on the model.
     * Never triggers a lazy load — critical because the service switches the
     * mysql connection to tenant DBs during hydration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $res = [];

        // ---- Always-present core fields ----
        $res['id']     = $this->id;
        $res['name']   = $this->name;
        $res['domain'] = $this->domain;
        $res['status'] = $this->status;
        $res['rate']   = $this->rate;

        $res['logo'] = $this->getFirstMediaUrl('Center') ?: asset('assets/img/avatars/1.png');

        $res['primary_images'] = $this->getMedia('PrimaryImage')
            ->map(fn ($media) => $media->getUrl())
            ->toArray();

        $res['created_at'] = $this->created_at;
        $res['is_favorite'] = (bool) ($this->resource->is_favorite ?? false);

        // ---- Optional relations (only if loaded / set by the service) ----

        if ($this->has('globalCategories')) {
            $res['global_categories'] = GlobalCategoryResource::collection($this->globalCategories);
        }

        if ($this->has('branches')) {
            $res['branches'] = BranchResource::collection($this->branches);
        }

        if ($this->has('categories')) {
            $res['categories'] = CategoryServiceResource::collection($this->categories);
        }

        if ($this->has('services')) {
            $res['services'] = ServiceResource::collection($this->services);
        }

        if ($this->has('packages')) {
            $res['packages'] = PackageResource::collection($this->packages);
        }

        if ($this->has('user_packages')) {
            $res['user_packages'] = UserPackageResource::collection($this->user_packages);
        }

        if ($this->has('user_used_packages')) {
            $res['user_used_packages'] = UserUsedPackageResource::collection($this->user_used_packages);
        }

        if ($this->has('workers')) {
            $res['workers'] = WorkerResource::collection($this->workers);
        }

        if ($this->has('about_us')) {
            $res['about_us'] = $this->about_us
                ? PageResource::make($this->about_us)
                : null;
        }

        if ($this->has('infos')) {
            $res['infos'] = InfoResource::collection($this->infos);
        }

        return $res;
    }

    /**
     * True if the key is already loaded on the model — as a relation OR as a
     * plain attribute assigned by the service — without triggering a lazy load.
     */
    private function has(string $key): bool
    {
        // Loaded as an Eloquent relation (->with('branches') or setRelation())
        if ($this->relationLoaded($key)) {
            return true;
        }

        // Assigned as a plain attribute by CenterService::hydrateCenterForList()
        // e.g. $center->branches = ...
        return array_key_exists($key, $this->resource->getAttributes());
    }
}