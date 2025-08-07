<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('faqs')->insert([
            [
                'question' => 'What is your return policy?',
                'answer' => 'You can return any item within 30 days of purchase with a valid receipt.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Do you offer international shipping?',
                'answer' => 'Yes, we ship worldwide. Shipping fees and delivery times vary by country.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'How can I track my order?',
                'answer' => 'After placing your order, you will receive a tracking number via email.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'How do I reset my password?',
                'answer' => 'Click on "Forgot Password" at the login page and follow the instructions to reset your password.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'Where can I view my purchase history?',
                'answer' => 'You can view your purchase history under the My Orders section in your account dashboard.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question' => 'How can I contact customer support?',
                'answer' => 'You can reach our customer support team via the Contact Us form or by calling our hotline.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
