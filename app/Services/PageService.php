<?php

namespace App\Services;

use App\Enums\PageEnum;
use App\Models\Page;

class PageService
{
    public function all()
    {
        return [
            'privacy_policy' => Page::where('type', PageEnum::PrivacyPolicy->value)->with('translations')->first(),
            'terms_conditions' => Page::where('type', PageEnum::TermsConditions->value)->with('translations')->first(),
            'about_us' => Page::where('type', PageEnum::AboutUs->value)->with('translations')->first(),
            'privacy_policy_app' => Page::where('type', PageEnum::PrivacyPolicyApp->value)->with('translations')->first(),
            'terms_of_use_app' => Page::where('type', PageEnum::TermsOfUseApp->value)->with('translations')->first(),
            'terms_of_service_app' => Page::where('type', PageEnum::TermsOfServiceApp->value)->with('translations')->first(),
            'about_us_app' => Page::where('type', PageEnum::AboutUsApp->value)->with('translations')->first(),
            'invoice_info' => Page::where('type', PageEnum::InvoiceInfo->value)->with('translations')->first()
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
