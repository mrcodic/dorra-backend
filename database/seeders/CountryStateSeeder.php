<?php



namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryStateSeeder extends Seeder
{
    public function run()
    {
        
        
        $countries = [
            [
                'id' => 1,
                'name' => 'Egypt',
          
                'states' => [
                    ['name' => 'Alexandria'],
                    ['name' => 'Aswan'],
                    ['name' => 'Asyut'],
                    ['name' => 'Beheira'],
                    ['name' => 'Beni Suef'],
                    ['name' => 'Cairo'],
                    ['name' => 'Dakahlia'],
                    ['name' => 'Damietta'],
                    ['name' => 'Faiyum'],
                    ['name' => 'Gharbia'],
                    ['name' => 'Giza'],
                    ['name' => 'Ismailia'],
                    ['name' => 'Kafr El Sheikh'],
                    ['name' => 'Luxor'],
                    ['name' => 'Matrouh'],
                    ['name' => 'Minya'],
                    ['name' => 'Monufia'],
                    ['name' => 'New Valley'],
                    ['name' => 'North Sinai'],
                    ['name' => 'Port Said'],
                    ['name' => 'Qalyubia'],
                    ['name' => 'Qena'],
                    ['name' => 'Red Sea'],
                    ['name' => 'Sharqia'],
                    ['name' => 'Sohag'],
                    ['name' => 'South Sinai'],
                    ['name' => 'Suez'],

                ],
            ],
            [
                'id' => 2,
                'name' => 'United Arab Emirates',
               
                'states' => [
                    ['name' => 'Abu Dhabi'],
                    ['name' => 'Dubai'],
                    ['name' => 'Sharjah'],
                    ['name' => 'Ajman'],
                    ['name' => 'Umm Al-Quwain'],
                    ['name' => 'Fujairah'],
                    ['name' => 'Ras Al Khaimah'],
                  
                   
                ],
            ],
            [
                'id' => 3,
                'name' => 'Saudi Arabia',
                
                'states' => [
                    ['name' => 'Riyadh'],
                    ['name' => 'Makkah'],
                    ['name' => 'Madinah'],
                    ['name' => 'Qassim'],
                    ['name' => 'Eastern Province'],
                    ['name' => 'Asir'],
                    ['name' => 'Tabuk'],
                    ['name' => 'Hail'],
                    ['name' => 'Northern Borders'],
                    ['name' => 'Jazan'],
                    ['name' => 'Najran'],
                    ['name' => 'Bahah'],
                    ['name' => 'Jawf'],
                ],
            ],
        ];

        foreach ($countries as $country) {
           
            $countryId = DB::table('countries')->insertGetId([
                'id' => $country['id'],
                'name' => $country['name'],
              
            ]);

          
            foreach ($country['states'] as $state) {
                DB::table('states')->insert([
                    'name' => $state['name'],
                    'country_id' => $countryId,
                ]);
            }
        }
    }
}
