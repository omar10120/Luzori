<?php

namespace App\Services;

use App\Models\Center;
use Illuminate\Support\Facades\Log;

trait NotifiesCenter
{
    /**
     * Resolve the center for the currently active tenant.
     */
    protected function resolveCurrentCenter(): ?Center
    {
        $center = Center::where('database', config('database.connections.mysql.database'))->first();

        if (!$center && session()->has('active_center_domain')) {
            $center = Center::where('domain', session('active_center_domain'))->first();
        }

        return $center;
    }

    /**
     * Fire a notification to the active center.
     *
     * @param string $type          short tag e.g. 'system', 'worker', 'branch'
     * @param array  $translations  ['en' => ['title' => ..., 'text' => ...], 'ar' => [...]]
     * @param array  $extra         extra FCM data merged into the payload (ids, action, etc.)
     * @param bool   $sendPush      whether to also send the FCM push
     */
    protected function notifyCenter(
        string $type,
        array $translations,
        array $extra = [],
        bool $sendPush = true
    ): void {
        try {
            $center = $this->resolveCurrentCenter();
            if (!$center) {
                return;
            }

            app(\App\Services\AppNotificationService::class)
                ->notifyCenterEvent($center, $type, $translations, $extra, $sendPush);
        } catch (\Throwable $e) {
            Log::error("Center notification ({$type}) failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}