<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use Illuminate\Support\Carbon;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $rows = [
            ['code' => 'prepress', 'name' => 'Prepress', 'requires_operator' => true,  'created_at' => $now, 'updated_at' => $now],
            ['code' => 'print',    'name' => 'Print',    'requires_operator' => true,  'created_at' => $now, 'updated_at' => $now],
            ['code' => 'finish',   'name' => 'Finish',   'requires_operator' => true,  'created_at' => $now, 'updated_at' => $now],
            ['code' => 'qc',       'name' => 'QC',       'requires_operator' => true,  'created_at' => $now, 'updated_at' => $now],
            ['code' => 'pack',     'name' => 'Pack',     'requires_operator' => false, 'created_at' => $now, 'updated_at' => $now],
        ];

        Station::upsert(
            $rows,
            ['code'],
            ['name', 'requires_operator', 'updated_at']
        );
    }
}
