<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['country_name' => 'Algeria', 'iso_code' => 'DZ', 'phone_code' => '+213'],
            ['country_name' => 'Bahrain', 'iso_code' => 'BH', 'phone_code' => '+973'],
            ['country_name' => 'Comoros', 'iso_code' => 'KM', 'phone_code' => '+269'],
            ['country_name' => 'Djibouti', 'iso_code' => 'DJ', 'phone_code' => '+253'],
            ['country_name' => 'Egypt', 'iso_code' => 'EG', 'phone_code' => '+20'],
            ['country_name' => 'Iraq', 'iso_code' => 'IQ', 'phone_code' => '+964'],
            ['country_name' => 'Jordan', 'iso_code' => 'JO', 'phone_code' => '+962'],
            ['country_name' => 'Kuwait', 'iso_code' => 'KW', 'phone_code' => '+965'],
            ['country_name' => 'Lebanon', 'iso_code' => 'LB', 'phone_code' => '+961'],
            ['country_name' => 'Libya', 'iso_code' => 'LY', 'phone_code' => '+218'],
            ['country_name' => 'Mauritania', 'iso_code' => 'MR', 'phone_code' => '+222'],
            ['country_name' => 'Morocco', 'iso_code' => 'MA', 'phone_code' => '+212'],
            ['country_name' => 'Oman', 'iso_code' => 'OM', 'phone_code' => '+968'],
            ['country_name' => 'Palestine', 'iso_code' => 'PS', 'phone_code' => '+970'],
            ['country_name' => 'Qatar', 'iso_code' => 'QA', 'phone_code' => '+974'],
            ['country_name' => 'Saudi Arabia', 'iso_code' => 'SA', 'phone_code' => '+966'],
            ['country_name' => 'Somalia', 'iso_code' => 'SO', 'phone_code' => '+252'],
            ['country_name' => 'Sudan', 'iso_code' => 'SD', 'phone_code' => '+249'],
            ['country_name' => 'Syria', 'iso_code' => 'SY', 'phone_code' => '+963'],
            ['country_name' => 'Tunisia', 'iso_code' => 'TN', 'phone_code' => '+216'],
            ['country_name' => 'United Arab Emirates', 'iso_code' => 'AE', 'phone_code' => '+971'],
            ['country_name' => 'Yemen', 'iso_code' => 'YE', 'phone_code' => '+967'],
        ];

        DB::table('country_codes')->insert($countries);
    }
}
