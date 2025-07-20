<?php

namespace Database\Seeders;

use App\Enums\Product\UnitEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimensionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dimensions = [
            [
                'name' => 'A4',
                'width' => 21.0,
                'height' => 29.7,
                'unit' => UnitEnum::CM,
            ],
            [
                'name' => 'A5',
                'width' => 14.8,
                'height' => 21.0,
                'unit' => UnitEnum::CM,
            ],
            [
                'name' => 'A6',
                'width' => 10.5,
                'height' => 14.8,
                'unit' => UnitEnum::CM,
            ],
            [
                'name' => 'B5',
                'width' => 17.6,
                'height' => 25.0,
                'unit' => UnitEnum::CM,
            ],
            [
                'name' => '23*32',
                'width' => 23,
                'height' => 32,
                'unit' => UnitEnum::CM,
            ],
            [
                'name' => '5*9',
                'width' => 9,
                'height' => 5,
                'unit' => UnitEnum::CM,
            ],
        ];

        foreach ($dimensions as $dimension) {
            DB::table('dimensions')->updateOrInsert(
                ['name' => $dimension['name']],
                array_merge($dimension, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

}
