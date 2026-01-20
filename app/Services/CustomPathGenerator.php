<?php

namespace App\Services;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CustomPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        $tenant = null;
        if (auth('center_api')->check()) {
            $tenant = auth('center_api')->user()->id . '/';
        } elseif (auth('center_user')->check()) {
            $tenant = auth('center_user')->user()->id . '/';
        } else {
            $tenant = 'admin/';
        }
        return $tenant . $this->getBasePath($media).'/';
    }

    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix.'/'.$media->getKey();
        }

        return $media->getKey();
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
