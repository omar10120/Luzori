<?php

namespace App\Traits;

trait UpdatedAtTrait
{
    public function getUpdatedAtAttribute()
    {
        if (str_contains(url()->current(), 'admin')) {
            if (auth('admin')->check()) {
                $date = \Carbon\Carbon::parse($this->attributes['updated_at']);
                return $date->diffForHumans(\Carbon\Carbon::now());
            }
        }
        return date('Y-m-d H:i:s', strtotime($this->attributes['updated_at']));
    }
}
