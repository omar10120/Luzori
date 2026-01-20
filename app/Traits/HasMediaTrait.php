<?php

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\InteractsWithMedia;

trait HasMediaTrait
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview-150')->width(150)->height(150);
        $this->addMediaConversion('preview-300')->width(300)->height(300);
        $this->addMediaConversion('preview-600')->width(600)->height(600);
    }

    public function getImageAttribute()
    {
        if ($this->getFirstMediaUrl(class_basename($this))) {
            return $this->getFirstMediaUrl(class_basename($this));
        } else {
            return asset('assets/img/avatars/1.png');
        }
    }
}
