<?php

namespace App\Services;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\DB;
class CustomPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        $tenant = $this->resolveTenant($media);

        return $tenant . $this->getBasePath($media) . '/';
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
    protected function resolveTenant(Media $media): string
    {
        $modelClass = $media->model_type;
        $modelId = $media->model_id;

        if (!$modelClass || !$modelId) {
            return '0/';
        }

        $model = $modelClass::find($modelId);

        if (!$model) {
            return '0/';
        }

        // ✅ Case 1: direct center_id
        if (isset($model->center_id)) {
            return $model->center_id . '/';
        }

        // ✅ Case 2: Worker → Branch → Center
        if (isset($model->branch_id)) {
            $branch = \App\Models\Branch::find($model->branch_id);
            if ($branch && $branch->center_id) {
                return $branch->center_id . '/';
            }
        }

        return '0/';
    }
}
