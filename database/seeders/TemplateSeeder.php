<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Template;
use App\Models\Product;

class TemplateSeeder extends Seeder
{
    public function run()
    {
        $product = Product::first();

        if (!$product) {
            $this->command->warn('No product found. Seeding skipped.');
            return;
        }

        $dummyJson = json_encode([
            "version" => "5.3.0",
            "objects" => [
                [
                    "type" => "rect",
                    "left" => 100,
                    "top" => 100,
                    "width" => 200,
                    "height" => 100,
                    "fill" => "#ff0000"
                ]
            ]
        ]);

        foreach (['Template One', 'Template Two', 'Template Three'] as $i => $name) {
            Template::create([
                'id' => Str::uuid(),
                'product_id' => $product->id,
                'name' => $name,
                'status' => [1, 2, 3][$i % 3],
                'json_data' => $dummyJson,
                'preview_png' => null,
                'source_svg' => null,
            ]);
        }
    }
}
