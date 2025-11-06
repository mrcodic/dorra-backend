<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // General settings
            ['key' => 'phone', 'value' => '01060538209'],
            ['key' => 'store_email', 'value' => 'dorraprint@gmail.com'],
            ['key' => 'delivery', 'value' => 30],
            ['key' => 'shipping_visibility', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'tax', 'value' => 0.1],

            // Landing Page Visibility Sections
            ['key' => 'navbar_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'hero_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'categories_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'designs_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'statistics_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'logo_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'testimonials_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'reviews_with_images_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'reviews_without_images_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'partners_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'faq_section', 'value' => true, 'group' => 'visibility_sections_landing'],

            // Statistics for Landing Page
            ['key' => 'customers', 'value' => 1200, 'group' => 'statistics_landing'],
            ['key' => 'orders', 'value' => 3400, 'group' => 'statistics_landing'],
            ['key' => 'rate', 'value' => 4.9, 'group' => 'statistics_landing'],

        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'] ?? null,
                ]
            );
        }
    }
}
