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
           CountryCodeSeeder::class,
           CountryStateSeeder::class,
          NotificationTypeSeeder::class,
           AdminSeeder::class,
           TemplateSeeder::class,
           PaymentSeeder::class,
           DimensionSeeder::class,
           TemplateTypeSeeder::class,
       ]);
    }
}
