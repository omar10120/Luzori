<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class AppNotification extends Model
{
    use Translatable, CreatedAtTrait, UpdatedAtTrait;

    protected $connection = 'central';
    protected $table = 'notifications';

    public $translationModel = AppNotificationTranslation::class;
    public $translationForeignKey = 'notification_id';
    public $translatedAttributes = ['title', 'text'];
    public $useTranslationFallback = true;
    protected $hidden = ['translations'];

    protected $fillable = [
        'image',
        'target_type',
        'status',
        'sent_count',
        'type',
        'created_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sent_count' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function appUsers(): MorphToMany
    {
        return $this->morphedByMany(AppUser::class, 'notifiable', 'notifiables', 'notification_id')
            ->withPivot('is_read')
            ->withTimestamps();
    }

    public function centers(): MorphToMany
    {
        return $this->morphedByMany(Center::class, 'notifiable', 'notifiables', 'notification_id')
            ->withPivot('is_read')
            ->withTimestamps();
    }
}