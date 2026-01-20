<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use App\Enums\PageEnum;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Page::create([
            'type' => PageEnum::PrivacyPolicy->value,
            'ar' => [
                'value' => 'سياسة الخصوصية',
            ],
            'en' => [
                'value' => 'privacy policy',
            ]
        ]);

        Page::create([
            'type' => PageEnum::TermsConditions->value,
            'ar' => [
                'value' => 'الشروط والأحكام',
            ],
            'en' => [
                'value' => 'terms conditions',
            ]
        ]);

        Page::create([
            'type' => PageEnum::AboutUs->value,
            'ar' => [
                'value' => 'معلومات عنا',
            ],
            'en' => [
                'value' => 'about us',
            ]
        ]);

        Page::create([
            'type' => PageEnum::WebsiteName->value,
            'ar' => [
                'value' => 'luzori',
            ],
            'en' => [
                'value' => 'luzori',
            ]
        ]);

        Page::create([
            'type' => PageEnum::WebsiteTitle->value,
            'ar' => [
                'value' => 'luzori',
            ],
            'en' => [
                'value' => 'luzori',
            ]
        ]);

        Page::create([
            'type' => PageEnum::WebsiteKeywords->value,
            'ar' => [
                'value' => 'luzori',
            ],
            'en' => [
                'value' => 'luzori',
            ]
        ]);

        Page::create([
            'type' => PageEnum::WebsiteDescription->value,
            'ar' => [
                'value' => 'luzori is company in DubaiI for meen care in UAE',
            ],
            'en' => [
                'value' => 'luzori is company in DubaiI for meen care in UAE',
            ]
        ]);

        Page::create([
            'type' => PageEnum::Auther->value,
            'ar' => [
                'value' => 'Techno Code Information Technology L.L.C',
            ],
            'en' => [
                'value' => 'Techno Code Information Technology L.L.C',
            ]
        ]);

        Page::create([
            'type' => PageEnum::Auther->value,
            'ar' => [
                'value' => 'luzori',
            ],
            'en' => [
                'value' => 'luzori',
            ]
        ]);

        Page::create([
            'type' => PageEnum::Address->value,
            'ar' => [
                'value' => 'UAE ABU DHABI',
            ],
            'en' => [
                'value' => 'UAE ABU DHABI',
            ]
        ]);

        Page::create([
            'type' => PageEnum::FooterText->value,
            'ar' => [
                'value' => '© Copyrights 2024. All rights reserved.',
            ],
            'en' => [
                'value' => '© Copyrights 2024. All rights reserved.',
            ]
        ]);
    }
}
