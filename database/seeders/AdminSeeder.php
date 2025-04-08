<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::query()->create([
            'first_name' =>'super',
            'last_name' => 'admin',
            'email' => 'super@admin.com',
            'phone_number' => '+201503464414',
                'password' => 123456789,
            'status' => 1,
        ]);
    }
}
