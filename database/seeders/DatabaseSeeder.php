<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CountryCodeSeeder::class,
//            CountryStateSeeder::class,
            NotificationTypeSeeder::class,
            AdminSeeder::class,
            SettingSeeder::class,
            PaymentSeeder::class,
            DimensionSeeder::class,
            TemplateTypeSeeder::class,
            SettingSeeder::class,
            StationSeeder::class,
            StationStatusesSeeder::class,
        ]);
    }
}
