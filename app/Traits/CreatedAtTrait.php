<?php

namespace App\Traits;

trait CreatedAtTrait
{
    function getCreatedAtAttribute()
    {
        if (str_contains(url()->current(), 'admin')) {
            if (auth('admin')->check()) {
                $date = \Carbon\Carbon::parse($this->attributes['created_at']);
                return $date->diffForHumans(\Carbon\Carbon::now());
            }
        }
        return date('Y-m-d H:i:s', strtotime($this->attributes['created_at']));
    }
}
