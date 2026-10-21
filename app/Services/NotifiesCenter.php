<?php

namespace App\Services;

use App\Models\Center;
use App\Models\InvoiceSetting;
use Illuminate\Support\Facades\Log;

trait NotifiesCenter
{
    /**
     * Cached result so we don't hit the DB on every notification in the same request.
     */
    private ?bool $allowNotificationCache = null;

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
     * Whether the current tenant has notifications enabled.
     * Reads invoice_settings.allow_notification (one row per tenant database).
     */
    protected function notificationsAllowed(): bool
    {
        if ($this->allowNotificationCache !== null) {
            return $this->allowNotificationCache;
        }

        try {
            $settings = InvoiceSetting::query()->first();
            $this->allowNotificationCache = (bool) ($settings?->allow_notification ?? false);
        } catch (\Throwable $e) {
            Log::error('Failed to read allow_notification setting: ' . $e->getMessage());
            $this->allowNotificationCache = false;
        }

        return $this->allowNotificationCache;
    }

    /**
     * Fire a notification to the active center — only if notifications are enabled.
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
            // Short-circuit: skip everything if the tenant disabled notifications.
            if (!$this->notificationsAllowed()) {
                return;
            }

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