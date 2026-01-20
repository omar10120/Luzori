<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterUserResource extends JsonResource
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
        $res['email'] = $this->email;
        $res['branch'] = BranchDetailsResource::make($this->branch);
        $res['country_code'] = $this->country_code;
        $res['phone'] = $this->phone;
        $res['image'] = $this->image;
        $res['role'] = $this->getRoleNames()->first();
        $res['permissions'] = $this->getAllPermissions()->select('name', 'name_ar', 'group')->groupBy('group');
        $res['created_at'] = $this->created_at;
        return $res;
    }
}
