<?php

namespace App\Services;

use App\Enums\PageEnum;
use App\Models\Page;

class PageService
{
    public function all()
    {
        return [
            'privacy_policy' => Page::where('type', PageEnum::PrivacyPolicy->value)->first(),
            'terms_conditions' => Page::where('type', PageEnum::TermsConditions->value)->first(),
            'about_us' => Page::where('type', PageEnum::AboutUs->value)->first()
        ];
    }

    public function edit($request)
    {
        foreach ($request['ar'] as $key => $value) {
            $page = Page::where('type', $value['key'])->first();
            $page->update([
                'ar' => [
                    'value' => $value['value'],
                ],
                'en' => [
                    'value' => $request['en'][$key]['value'],
                ],
            ]);
        }
        return true;
    }

    public function privacyPolicy()
    {
        return Page::where('type', PageEnum::PrivacyPolicy->value)->first();
    }

    public function termsConditions()
    {
        return Page::where('type', PageEnum::TermsConditions->value)->first();
    }

    public function aboutUs()
    {
        return Page::where('type', PageEnum::AboutUs->value)->first();
    }
}
