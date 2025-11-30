<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Position::create([
            'name' => 'top-left',
            'print_x' => 360,
            'print_y' => 660,
            'print_height' => 480,
            'print_width' => 540,
        ]);
    }
}
