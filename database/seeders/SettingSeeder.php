<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        Setting::updateOrCreate(
            ['key' => 'delivery'],
            ['value' => 30]
        );

        Setting::updateOrCreate(
            ['key' => 'tax'],
            ['value' => 0.1]
        );
    }
}
