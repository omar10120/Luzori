<?php

namespace App\Traits;

use App\Models\Center;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        $ownImage = $this->getFirstMediaUrl(class_basename($this));

        if ($ownImage) {
            return $ownImage;
        }

        return $this->getCenterProfileImageFallback();
    }

    /**
     * Prefer authenticated center-user profile image (ProfileController),
     * then center logo, then static avatar.
     */
    protected function getCenterProfileImageFallback(): string
    {
        try {
            // Same image managed by CenterAPI ProfileController
            if (class_basename($this) !== 'CenterUser') {
                $centerUser = auth('center_api')->user() ?? auth('center_user')->user();
                if ($centerUser) {
                    $profileImage = $centerUser->getFirstMediaUrl('CenterUser');
                    if ($profileImage) {
                        return $profileImage;
                    }
                }
            }

            $center = $this->resolveActiveCenterForFallback();
            if ($center) {
                $logo = $center->getFirstMediaUrl('Center');
                if ($logo) {
                    return $logo;
                }
            }
        } catch (\Throwable $e) {
            // Ignore and use static fallback
        }

        return asset('assets/img/avatars/Avatar.jpg');
    }

    protected function resolveActiveCenterForFallback(): ?Center
    {
        $host = request()->getHost();

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return Center::where('domain', 'center')->first()
                ?? Center::where('database', config('database.connections.mysql.database'))->first();
        }

        if (session()->has('active_center_domain')) {
            $center = Center::where('domain', session('active_center_domain'))->first();
            if ($center) {
                return $center;
            }
        }

        $parts = explode('.', $host);
        $subdomain = count($parts) > 2 && $parts[0] !== 'www' ? $parts[0] : null;
        if ($subdomain && $subdomain !== 'dashboard') {
            $center = Center::where('domain', $subdomain)->first();
            if ($center) {
                return $center;
            }
        }

        return Center::where('database', config('database.connections.mysql.database'))->first();
    }
}
