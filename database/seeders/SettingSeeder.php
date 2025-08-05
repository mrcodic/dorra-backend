<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'delivery', 'value' => 30],
            ['key' => 'tax', 'value' => 0.1],
            ['key' => 'navbar_section', 'value' => true],
            ['key' => 'hero_section', 'value' => true],
            ['key' => 'categories_section', 'value' => true],
            ['key' => 'designs_section', 'value' => true],
            ['key' => 'statistics_section', 'value' => true],
            ['key' => 'logo_section', 'value' => true],
            ['key' => 'testimonials_section', 'value' => true],
            ['key' => 'partners_section', 'value' => true],
            ['key' => 'faq_section', 'value' => true],
        ];

        Setting::upsert($settings, ['key'], ['value']);
    }
}
