<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Seed Paymob gateway
        $paymobConfig = config('services.paymob');

        DB::table('payment_gateways')->updateOrInsert(
            ['code' => 'paymob'],
            [
                'name' => 'Paymob',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $paymobGateway = DB::table('payment_gateways')->where('code', 'paymob')->first();

        if (!$paymobGateway) {
            throw new \Exception("Paymob gateway not found.");
        }

        // 🔹 Seed Paymob methods
        $methods = [
            ['name' => 'Debit/Credit Card', 'code' => 'paymob_card'],
            ['name' => 'Wallet', 'code' => 'paymob_wallet'],
            ['name' => 'Aman', 'code' => 'paymob_kiosk'],
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

        // 🔹 Seed Cash on Delivery (no gateway)
        DB::table('payment_methods')->updateOrInsert(
            ['code' => 'cash_on_delivery'],
            [
                'payment_gateway_id' => null, // COD doesn't belong to an external gateway
                'name' => 'Cash on Delivery',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
