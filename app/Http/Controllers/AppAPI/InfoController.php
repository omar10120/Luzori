<?php

namespace App\Http\Controllers\AppAPI;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Enums\PageEnum;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    /**
     * Get app legal and about information.
     */
    public function index(Request $request)
    {
        $pageTypes = [
            'privacy_policy' => PageEnum::PrivacyPolicyApp->value,
            'terms_of_use' => PageEnum::TermsOfUseApp->value,
            'terms_of_service' => PageEnum::TermsOfServiceApp->value,
            'about_us' => PageEnum::AboutUsApp->value,
        ];

        $data = [];
        $locales = ['ar', 'en'];

        foreach ($pageTypes as $key => $type) {
            $page = Page::with('translations')->where('type', $type)->first();
            
            $translations = [];
            foreach ($locales as $locale) {
                $translations[$locale] = $page ? ($page->translate($locale)?->value ?? '') : '';
            }
            
            $data[$key] = $translations;
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
