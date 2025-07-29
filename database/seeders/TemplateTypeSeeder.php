<?php

namespace Database\Seeders;

use App\Enums\Mockup\TypeEnum;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TemplateTypeSeeder extends Seeder
{
    public function run()
    {
        foreach (TypeEnum::cases() as $type) {
            DB::table('types')->updateOrInsert(
                ['value' => $type->value],
                [
                    'value' => $type->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
