<?php

namespace App\Services;

use App\Models\Invoice_Settings;

class InvoiceSettingsService
{
    use NotifiesCenter;

    public function first(): Invoice_Settings
    {
        return Invoice_Settings::query()->firstOrCreate(['id' => 1]);
    }

    public function update(array $data): Invoice_Settings
    {
        $settings = $this->first();

        // Snapshot the flag BEFORE update so we can detect a real transition.
        $wasSmsAllow = (bool) $settings->sms_allow;

        $settings->update($data);

        // `update()` mutates the model in memory, so $settings->sms_allow
        // now reflects the freshly-stored value.
        if (! $wasSmsAllow && (bool) $settings->sms_allow) {
            $this->sendSmsAllowEnabledNotification();
        }

        return $settings->fresh();
    }

    /**
     * Tell the active center that SMS sending has just been enabled.
     * Best-effort: the trait already catches + logs every error internally.
     */
    private function sendSmsAllowEnabledNotification(): void
    {
        $this->notifyCenter(
            'settings',
            [
                'en' => [
                    'title' => 'SMS Enabled',
                    'text'  => 'SMS notifications have been enabled for this center.',
                ],
                'ar' => [
                    'title' => 'تم تفعيل الرسائل النصية',
                    'text'  => 'تم تفعيل إرسال الرسائل النصية لهذا المركز.',
                ],
            ],
            [
                'action'      => 'sms_allow_enabled',
                'settings_id' => '1',
            ]
        );
    }
}