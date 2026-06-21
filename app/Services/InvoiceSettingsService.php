<?php

namespace App\Services;

use App\Models\Invoice_Settings;

class InvoiceSettingsService
{
    public function first(): Invoice_Settings
    {
        return Invoice_Settings::query()->firstOrCreate(['id' => 1]);
    }

    public function update(array $data): Invoice_Settings
    {
        $settings = $this->first();
        $settings->update($data);

        return $settings->fresh();
    }
}
