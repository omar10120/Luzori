<?php

namespace App\Services;

use App\Enums\SettingEnum;
use App\Enums\PageEnum;
use App\Models\Page;
use App\Models\Setting;

class SettingService
{
    public function all()
    {
        $item = [
            'WebsiteName' => Page::with(['translation'])->where('type', PageEnum::WebsiteName->value)->first(),
            'WebsiteTitle' => Page::with(['translation'])->where('type', PageEnum::WebsiteTitle->value)->first(),
            'WebsiteDescription' => Page::with(['translation'])->where('type', PageEnum::WebsiteDescription->value)->first(),
            'Auther' => Page::with(['translation'])->where('type', PageEnum::Auther->value)->first(),
            'Address' => Page::with(['translation'])->where('type', PageEnum::Address->value)->first(),
            'FooterText' => Page::with(['translation'])->where('type', PageEnum::FooterText->value)->first(),
        ];

        return [
            'item' => $item,
            'language' => Setting::where('key', SettingEnum::language->value)->first(),
            'tips' => Setting::where('key', SettingEnum::tips->value)->first(),
            'invoice_info' => Setting::where('key', SettingEnum::invoice_info->value)->first(),
            'image' => Setting::first()->image,
        ];
    }

    public function edit($request)
    {
        foreach ($request['ar'] as $key => $value) {
            $page = Page::where('type', $key)->first();
            $page->update([
                'ar' => [
                    'value' => $value,
                ],
                'en' => [
                    'value' => $request['en'][$key],
                ],
            ]);
        }

        $setting = Setting::where('key', SettingEnum::language->value)->first();
        $setting->update([
            'value' => $request['language']
        ]);

        $setting = Setting::where('key', SettingEnum::tips->value)->first();
        $setting->update([
            'value' => $request['tips']
        ]);

        $setting = Setting::where('key', SettingEnum::invoice_info->value)->first();
        $setting->update([
            'value' => $request['invoice_info']
        ]);

        if (isset($request['image'])) {
            $setting = Setting::first();
            $setting->clearMediaCollection('Setting');
            $setting->addMedia($request['image'])->toMediaCollection('Setting');
        }

        return true;
    }
}
