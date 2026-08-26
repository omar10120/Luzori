<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirebaseSetting extends Model
{
    protected $connection = 'central';
    protected $table = 'firebase_settings';

    protected $fillable = [
        'service_account_json',
        'api_key',
        'auth_domain',
        'project_id',
        'storage_bucket',
        'messaging_sender_id',
        'app_id',
        'measurement_id',
    ];

    public static function current(): ?self
    {
        return static::query()->first();
    }

    public function serviceAccountArray(): ?array
    {
        if (empty($this->service_account_json)) {
            return null;
        }

        $decoded = json_decode($this->service_account_json, true);

        return is_array($decoded) ? $decoded : null;
    }
}
