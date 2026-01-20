<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
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
        $res['membership_no'] = $this->membership_no;
        $res['percent'] = $this->percent;
        $res['start_at'] = $this->start_at;
        $res['end_at'] = $this->end_at;
        $res['user'] = null;
        if ($this->user) {
            $res['user'] = UserResource::make($this->user);
        }
        $res['created_at'] = $this->created_at;
        $res['created_by'] = $this->created_user->name ?? '-';
        return $res;
    }
}
