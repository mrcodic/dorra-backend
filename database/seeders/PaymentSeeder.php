<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $paymobConfig = config('services.paymob');

        $gatewayId = DB::table('payment_gateways')->updateOrInsert(
            ['code' => 'paymob'],
            [
                'name' => 'Paymob',
                'api_key' => $paymobConfig['api_key'] ?? null,
                'config' => json_encode([
                    'api_key' => $paymobConfig['api_key'] ?? null,
                    'kiosk_integration_id' => $paymobConfig['kiosk_integration_id'] ?? null,
                    'card_integration_id' => $paymobConfig['card_integration_id'] ?? null,
                    'wallet_integration_id' => $paymobConfig['wallet_integration_id'] ?? null,
                    'hmac' => $paymobConfig['hmac'] ?? null,
                    'currency' => $paymobConfig['currency'] ?? 'EGP',
                ]),
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $paymobGateway = DB::table('payment_gateways')->where('code', 'paymob')->first();

        if (!$paymobGateway) {
            throw new \Exception("Paymob gateway not found.");
        }

        $methods = [
            ['name' => 'Card', 'code' => 'paymob_card'],
            ['name' => 'Wallet', 'code' => 'paymob_wallet'],
            ['name' => 'Kiosk', 'code' => 'paymob_kiosk'],
        ];

        foreach ($methods as $method) {
            DB::table('payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                [
                    'payment_gateway_id' => $paymobGateway->id,
                    'name' => $method['name'],
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
