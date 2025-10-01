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
            ['code' => 'prepress', 'name' => 'Prepress', 'requires_operator' => true,  'workflow_order' => 1, 'is_terminal' => false, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'print',    'name' => 'Print',    'requires_operator' => true,  'workflow_order' => 2, 'is_terminal' => false, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'finish',   'name' => 'Finish',   'requires_operator' => true,  'workflow_order' => 3, 'is_terminal' => false, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'qc',       'name' => 'QC',       'requires_operator' => true,  'workflow_order' => 4, 'is_terminal' => false, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'pack',     'name' => 'Pack',     'requires_operator' => false, 'workflow_order' => 5, 'is_terminal' => true,  'created_at' => $now, 'updated_at' => $now],
        ];

        Station::upsert(
            $rows,
            ['code'],
            ['name', 'requires_operator', 'workflow_order', 'is_terminal', 'updated_at']
        );
    }
}
