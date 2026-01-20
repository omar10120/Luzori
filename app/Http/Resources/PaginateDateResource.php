<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaginateDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $res['current_page'] = $this->currentPage();

        $firstPageUrl = $this->url(1);
        $lastPageUrl = $this->url($this->lastPage());

        $res['first_page_url'] = $firstPageUrl;
        $res['last_page_url'] = $lastPageUrl;
        $res['next_page_url'] = $this->nextPageUrl();
        $res['prev_page_url'] = $this->previousPageUrl();
        $res['first_page'] = 1;
        $res['last_page'] = $this->lastPage();
        $res['first_item'] = $this->firstItem();
        $res['last_item'] = $this->lastItem();
        $res['per_page'] = $this->perPage();
        $res['total'] = $this->total();
        return $res;
    }
}
