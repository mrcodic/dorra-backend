<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;

class CountryStateSeeder extends Seeder
{
    public function run()
    {
        $countries = [
            [
                'id' => 1,
                'name' => [
                    'en' => 'Egypt',
                    'ar' => 'مصر',
                ],
                'states' => [
                    ['name' => ['en' => 'Alexandria', 'ar' => 'الإسكندرية']],
                    ['name' => ['en' => 'Aswan', 'ar' => 'أسوان']],
                    ['name' => ['en' => 'Asyut', 'ar' => 'أسيوط']],
                    ['name' => ['en' => 'Beheira', 'ar' => 'البحيرة']],
                    ['name' => ['en' => 'Beni Suef', 'ar' => 'بني سويف']],
                    ['name' => ['en' => 'Cairo', 'ar' => 'القاهرة']],
                    ['name' => ['en' => 'Dakahlia', 'ar' => 'الدقهلية']],
                    ['name' => ['en' => 'Damietta', 'ar' => 'دمياط']],
                    ['name' => ['en' => 'Faiyum', 'ar' => 'الفيوم']],
                    ['name' => ['en' => 'Gharbia', 'ar' => 'الغربية']],
                    ['name' => ['en' => 'Giza', 'ar' => 'الجيزة']],
                    ['name' => ['en' => 'Ismailia', 'ar' => 'الإسماعيلية']],
                    ['name' => ['en' => 'Kafr El Sheikh', 'ar' => 'كفر الشيخ']],
                    ['name' => ['en' => 'Luxor', 'ar' => 'الأقصر']],
                    ['name' => ['en' => 'Matrouh', 'ar' => 'مطروح']],
                    ['name' => ['en' => 'Minya', 'ar' => 'المنيا']],
                    ['name' => ['en' => 'Monufia', 'ar' => 'المنوفية']],
                    ['name' => ['en' => 'New Valley', 'ar' => 'الوادي الجديد']],
                    ['name' => ['en' => 'North Sinai', 'ar' => 'شمال سيناء']],
                    ['name' => ['en' => 'Port Said', 'ar' => 'بورسعيد']],
                    ['name' => ['en' => 'Qalyubia', 'ar' => 'القليوبية']],
                    ['name' => ['en' => 'Qena', 'ar' => 'قنا']],
                    ['name' => ['en' => 'Red Sea', 'ar' => 'البحر الأحمر']],
                    ['name' => ['en' => 'Sharqia', 'ar' => 'الشرقية']],
                    ['name' => ['en' => 'Sohag', 'ar' => 'سوهاج']],
                    ['name' => ['en' => 'South Sinai', 'ar' => 'جنوب سيناء']],
                    ['name' => ['en' => 'Suez', 'ar' => 'السويس']],
                ],
            ],
            [
                'id' => 2,
                'name' => [
                    'en' => 'United Arab Emirates',
                    'ar' => 'الإمارات العربية المتحدة',
                ],
                'states' => [
                    ['name' => ['en' => 'Abu Dhabi', 'ar' => 'أبوظبي']],
                    ['name' => ['en' => 'Dubai', 'ar' => 'دبي']],
                    ['name' => ['en' => 'Sharjah', 'ar' => 'الشارقة']],
                    ['name' => ['en' => 'Ajman', 'ar' => 'عجمان']],
                    ['name' => ['en' => 'Umm Al-Quwain', 'ar' => 'أم القيوين']],
                    ['name' => ['en' => 'Fujairah', 'ar' => 'الفجيرة']],
                    ['name' => ['en' => 'Ras Al Khaimah', 'ar' => 'رأس الخيمة']],
                ],
            ],
            [
                'id' => 3,
                'name' => [
                    'en' => 'Saudi Arabia',
                    'ar' => 'المملكة العربية السعودية',
                ],
                'states' => [
                    ['name' => ['en' => 'Riyadh', 'ar' => 'الرياض']],
                    ['name' => ['en' => 'Makkah', 'ar' => 'مكة المكرمة']],
                    ['name' => ['en' => 'Madinah', 'ar' => 'المدينة المنورة']],
                    ['name' => ['en' => 'Qassim', 'ar' => 'القصيم']],
                    ['name' => ['en' => 'Eastern Province', 'ar' => 'المنطقة الشرقية']],
                    ['name' => ['en' => 'Asir', 'ar' => 'عسير']],
                    ['name' => ['en' => 'Tabuk', 'ar' => 'تبوك']],
                    ['name' => ['en' => 'Hail', 'ar' => 'حائل']],
                    ['name' => ['en' => 'Northern Borders', 'ar' => 'الحدود الشمالية']],
                    ['name' => ['en' => 'Jazan', 'ar' => 'جازان']],
                    ['name' => ['en' => 'Najran', 'ar' => 'نجران']],
                    ['name' => ['en' => 'Bahah', 'ar' => 'الباحة']],
                    ['name' => ['en' => 'Jawf', 'ar' => 'الجوف']],
                ],
            ],
        ];

        foreach ($countries as $country) {
            $countryModel = Country::updateOrCreate(
                ['id' => $country['id']], // match by ID
                ['name' => $country['name']]
            );

            foreach ($country['states'] as $state) {
                State::updateOrCreate(
                    [
                        'country_id' => $countryModel->id,
                        'name->en' => $state['name']['en'], // match by English name
                    ],
                    ['name' => $state['name']]
                );
            }
        }
    }
}
