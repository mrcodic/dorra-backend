<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            ['name' => 'Order status updated'],
            ['name' => 'Added to a new team'],
            ['name' => 'Someone in my team requests design review'],
            ['name' => 'Offers on products are placed'],
        ];

        DB::table('notification_types')->insert($notifications);
    }
}
