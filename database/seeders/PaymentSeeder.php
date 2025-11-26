<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1) Paymob Gateway
        |--------------------------------------------------------------------------
        */
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

        // Paymob Methods
        $paymobMethods = [
            ['name' => 'Debit/Credit Card', 'code' => 'paymob_card', 'file_name' => 'card.png'],
            ['name' => 'Wallet', 'code' => 'paymob_wallet', 'file_name' => 'wallet.png'],
            ['name' => 'Aman', 'code' => 'paymob_kiosk', 'file_name' => 'aman.png'],
        ];

        foreach ($paymobMethods as $method) {
            DB::table('payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                [
                    'payment_gateway_id' => $paymobGateway->id,
                    'name' => $method['name'],
                    'file_name' => $method['file_name'],
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Fawry Gateway
        |--------------------------------------------------------------------------
        */
        DB::table('payment_gateways')->updateOrInsert(
            ['code' => 'fawry'],
            [
                'name' => 'Fawry',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $fawryGateway = DB::table('payment_gateways')->where('code', 'fawry')->first();
        if (!$fawryGateway) {
            throw new \Exception("Fawry gateway not found.");
        }

        // Fawry Methods
        $fawryMethods = [
            ['code' => 'PayAtFawry', 'name' => 'Pay At Fawry', 'file_name' => 'fawry.png'],
            ['code' => 'MWALLET', 'name' => 'Mobile WALLET', 'file_name' => 'wallet.png'],
            ['code' => 'CARD', 'name' => 'CARD', 'file_name' => 'card.png'],
        ];

        foreach ($fawryMethods as $method) {
            DB::table('payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                [
                    'payment_gateway_id' => $fawryGateway->id,
                    'name' => $method['name'],
                    'file_name' => $method['file_name'],
                    
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Cash on Delivery (No Gateway)
        |--------------------------------------------------------------------------
        */
        DB::table('payment_methods')->updateOrInsert(
            ['code' => 'cash_on_delivery'],
            [
                'payment_gateway_id' => null,
                'name' => 'Cash on Delivery',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
