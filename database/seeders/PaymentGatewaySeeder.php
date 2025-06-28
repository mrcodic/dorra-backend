<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;


class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $paymob = PaymentGateway::create([
            'name' => 'Paymob',
            'code' => 'paymob',
            'api_key' => env('PAYMOB_API_KEY'),
            'config' => [
                'integration_id' => env('PAYMOB_INTEGRATION_ID'),
                'iframe_id' => env('PAYMOB_IFRAME_ID'),
            ],
            'active' => true,
        ]);

        $paymob->paymentMethods()->createMany([
            [
                'name' => 'Visa / MasterCard',
                'code' => 'credit_card',
                'active' => true,
            ],
            [
                'name' => 'Mobile Wallet',
                'code' => 'wallet',
                'active' => true,
            ],
            [
                'name' => 'Aman',
                'code' => 'kiosk',
                'active' => true,
            ],
        ]);
    }
}
